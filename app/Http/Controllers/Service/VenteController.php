<?php
// app/Http/Controllers/Service/VenteController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Vente;
use App\Models\Service;
use App\Models\Devise;
use App\Models\Temps;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class VenteController extends Controller
{
    /**
     * Afficher la liste des ventes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $query = Vente::where('id_service', $service->id_service)
            ->with(['devise', 'temps']);
        
        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('facture_numero', 'like', "%{$search}%");
            });
        }
        
        $ventes = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques
        $devise = session('devise', 'USD');
        $stats = [
            'total' => Vente::where('id_service', $service->id_service)->count(),
            'total_montant' => Vente::where('id_service', $service->id_service)->sum('montant'),
            'mois_courant' => Vente::where('id_service', $service->id_service)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
            'moyenne_vente' => Vente::where('id_service', $service->id_service)->avg('montant') ?? 0
        ];
        
        return view('service.ventes.index', compact('ventes', 'stats', 'service', 'devise'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $devises = Devise::all();
        
        // Générer une référence automatique
        $reference = 'VTE-' . date('Ymd') . '-' . rand(1000, 9999);
        
        return view('service.ventes.create', compact('service', 'devises', 'reference'));
    }
    
    /**
     * Enregistrer une nouvelle vente
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $request->validate([
            'reference' => 'required|string|max:50|unique:ventes,reference',
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'facture_numero' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'date_vente' => 'nullable|date',
        ]);
        
        $dateVente = $request->date_vente ? Carbon::parse($request->date_vente) : now();
        $temps = Temps::firstOrCreate(
            ['date' => $dateVente->format('Y-m-d')],
            [
                'jour' => $dateVente->day,
                'mois' => $dateVente->month,
                'trimestre' => ceil($dateVente->month / 3),
                'annee' => $dateVente->year,
                'type_periode' => $dateVente->isWeekend() ? 'weekend' : 'ouvre',
                'est_cloturee' => false
            ]
        );
        
        $devise = Devise::find($request->id_devise);
        
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        $vente = Vente::create([
            'reference' => $request->reference,
            'id_service' => $service->id_service,
            'id_utilisateur' => $user->id_utilisateur,
            'id_temps' => $temps->id_temps,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'facture_numero' => $request->facture_numero,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $indicateurCA = Indicateur::where('code_kpi', 'CA')->first();
        $activiteVente = Activite::where('type_activite', 'vente')->first();
        
        if ($indicateurCA && $activiteVente) {
            FinanceFait::create([
                'montant_original' => $request->montant,
                'montant_cdf' => $montantCDF,
                'montant_usd' => $montantUSD,
                'montant_eur' => $montantEUR,
                'description' => $request->description,
                'id_temps' => $temps->id_temps,
                'id_service' => $service->id_service,
                'id_utilisateur' => $user->id_utilisateur,
                'id_indicateur' => $indicateurCA->id_indicateur,
                'id_activite' => $activiteVente->id_activite,
                'id_devise_originale' => $request->id_devise,
                'created_at' => now(),
            ]);
        }
        
        return redirect()->route('service.ventes.index')
            ->with('success', 'Vente enregistrée avec succès');
    }
    
    /**
     * Afficher les détails d'une vente
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $vente = Vente::with(['devise', 'temps', 'utilisateur'])
            ->where('id_service', $service->id_service)
            ->findOrFail($id);
        
        $devise = session('devise', 'USD');
        
        return view('service.ventes.show', compact('vente', 'service', 'devise'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $vente = Vente::where('id_service', $service->id_service)->findOrFail($id);
        $devises = Devise::all();
        
        return view('service.ventes.edit', compact('vente', 'service', 'devises'));
    }
    
    /**
     * Mettre à jour une vente
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $vente = Vente::where('id_service', $service->id_service)->findOrFail($id);
        
        $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('ventes', 'reference')->ignore($id, 'id_vente')],
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'facture_numero' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);
        
        $vente->update([
            'reference' => $request->reference,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'facture_numero' => $request->facture_numero,
            'description' => $request->description,
            'updated_at' => now(),
        ]);
        
        $devise = Devise::find($request->id_devise);
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'vente');
        })->where('id_service', $service->id_service)
          ->where('description', $vente->description)
          ->update([
              'montant_original' => $request->montant,
              'montant_cdf' => $montantCDF,
              'montant_usd' => $montantUSD,
              'montant_eur' => $montantEUR,
              'id_devise_originale' => $request->id_devise,
          ]);
        
        return redirect()->route('service.ventes.index')
            ->with('success', 'Vente modifiée avec succès');
    }
    
    /**
     * Supprimer une vente
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $vente = Vente::where('id_service', $service->id_service)->findOrFail($id);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'vente');
        })->where('id_service', $service->id_service)
          ->where('description', $vente->description)
          ->delete();
        
        $vente->delete();
        
        return redirect()->route('service.ventes.index')
            ->with('success', 'Vente supprimée avec succès');
    }
    
    /**
     * Exporter les ventes
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $service = $user->service ?? Service::first();
        
        $query = Vente::where('id_service', $service->id_service);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $ventes = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'ventes_' . $service->nom_service . '_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Référence', 'Date', 'Montant', 'Devise', 'Facture', 'Description'], ';');
        
        foreach ($ventes as $vente) {
            fputcsv($handle, [
                $vente->reference,
                $vente->created_at->format('d/m/Y'),
                $vente->montant,
                $vente->devise->code ?? 'USD',
                $vente->facture_numero ?? '-',
                $vente->description ?? '-'
            ], ';');
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}