<?php
// app/Http/Controllers/Service/AchatController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Achat;
use App\Models\Service;
use App\Models\Devise;
use App\Models\Temps;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AchatController extends Controller
{
    /**
     * Afficher la liste des achats
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $query = Achat::where('id_service', $service->id_service)
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
                  ->orWhere('bon_commande', 'like', "%{$search}%");
            });
        }
        
        $achats = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques (sans fournisseur_id)
        $devise = session('devise', 'USD');
        $stats = [
            'total' => Achat::where('id_service', $service->id_service)->count(),
            'total_montant' => Achat::where('id_service', $service->id_service)->sum('montant'),
            'mois_courant' => Achat::where('id_service', $service->id_service)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
            'moyenne_achat' => Achat::where('id_service', $service->id_service)->avg('montant') ?? 0
        ];
        
        return view('service.achats.index', compact('achats', 'stats', 'service', 'devise'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $devises = Devise::all();
        
        // Générer une référence automatique
        $reference = $this->generateReference($service->id_service);
        
        return view('service.achats.create', compact('service', 'devises', 'reference'));
    }
    
    /**
     * Enregistrer un nouvel achat
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $request->validate([
            'reference' => 'required|string|max:50|unique:achats,reference',
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'bon_commande' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'date_achat' => 'nullable|date',
        ]);
        
        // Créer ou récupérer l'enregistrement temps
        $dateAchat = $request->date_achat ? Carbon::parse($request->date_achat) : now();
        $temps = Temps::firstOrCreate(
            ['date' => $dateAchat->format('Y-m-d')],
            [
                'jour' => $dateAchat->day,
                'mois' => $dateAchat->month,
                'trimestre' => ceil($dateAchat->month / 3),
                'annee' => $dateAchat->year,
                'type_periode' => $dateAchat->isWeekend() ? 'weekend' : 'ouvre',
                'est_cloturee' => false
            ]
        );
        
        // Récupérer la devise
        $devise = Devise::find($request->id_devise);
        
        // Calculer les montants convertis
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        // Créer l'achat
        $achat = Achat::create([
            'reference' => $request->reference,
            'id_service' => $service->id_service,
            'id_utilisateur' => $user->id_utilisateur,
            'id_temps' => $temps->id_temps,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'bon_commande' => $request->bon_commande,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Enregistrer dans la table des faits financiers
        $indicateurDepense = Indicateur::where('code_kpi', 'DEPENSE')->first();
        $activiteAchat = Activite::where('type_activite', 'achat')->first();
        
        if ($indicateurDepense && $activiteAchat) {
            FinanceFait::create([
                'montant_original' => $request->montant,
                'montant_cdf' => $montantCDF,
                'montant_usd' => $montantUSD,
                'montant_eur' => $montantEUR,
                'description' => $request->description,
                'id_temps' => $temps->id_temps,
                'id_service' => $service->id_service,
                'id_utilisateur' => $user->id_utilisateur,
                'id_indicateur' => $indicateurDepense->id_indicateur,
                'id_activite' => $activiteAchat->id_activite,
                'id_devise_originale' => $request->id_devise,
                'created_at' => now(),
            ]);
        }
        
        return redirect()->route('service.achats.index')
            ->with('success', 'Achat enregistré avec succès');
    }
    
    /**
     * Afficher les détails d'un achat
     */
    public function show($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $achat = Achat::with(['devise', 'temps', 'utilisateur'])
            ->where('id_service', $service->id_service)
            ->findOrFail($id);
        
        $devise = session('devise', 'USD');
        
        return view('service.achats.show', compact('achat', 'service', 'devise'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $achat = Achat::where('id_service', $service->id_service)->findOrFail($id);
        $devises = Devise::all();
        
        return view('service.achats.edit', compact('achat', 'service', 'devises'));
    }
    
    /**
     * Mettre à jour un achat
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $achat = Achat::where('id_service', $service->id_service)->findOrFail($id);
        
        $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('achats', 'reference')->ignore($id, 'id_achat')],
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'bon_commande' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);
        
        $achat->update([
            'reference' => $request->reference,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'bon_commande' => $request->bon_commande,
            'description' => $request->description,
            'updated_at' => now(),
        ]);
        
        // Mettre à jour la table des faits
        $devise = Devise::find($request->id_devise);
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'achat');
        })->where('id_service', $service->id_service)
          ->where('description', $achat->description)
          ->update([
              'montant_original' => $request->montant,
              'montant_cdf' => $montantCDF,
              'montant_usd' => $montantUSD,
              'montant_eur' => $montantEUR,
              'id_devise_originale' => $request->id_devise,
          ]);
        
        return redirect()->route('service.achats.index')
            ->with('success', 'Achat modifié avec succès');
    }
    
    /**
     * Supprimer un achat
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $achat = Achat::where('id_service', $service->id_service)->findOrFail($id);
        
        // Supprimer l'entrée dans la table des faits
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'achat');
        })->where('id_service', $service->id_service)
          ->where('description', $achat->description)
          ->delete();
        
        $achat->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Achat supprimé avec succès']);
        }
        
        return redirect()->route('service.achats.index')
            ->with('success', 'Achat supprimé avec succès');
    }
    
    /**
     * Exporter les achats
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $query = Achat::where('id_service', $service->id_service);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $achats = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'achats_' . $service->nom_service . '_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Référence', 'Date', 'Montant', 'Devise', 'Bon de commande', 'Description'], ';');
        
        foreach ($achats as $achat) {
            fputcsv($handle, [
                $achat->reference,
                $achat->created_at->format('d/m/Y'),
                $achat->montant,
                $achat->devise->code ?? 'USD',
                $achat->bon_commande ?? '-',
                $achat->description ?? '-'
            ], ';');
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Générer une référence unique
     */
    private function generateReference($serviceId)
    {
        $prefix = 'ACH-' . date('Ymd') . '-';
        $lastAchat = Achat::where('reference', 'like', $prefix . '%')
            ->orderBy('reference', 'desc')
            ->first();
        
        if ($lastAchat) {
            $lastNum = intval(substr($lastAchat->reference, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        
        return $prefix . $newNum;
    }
}