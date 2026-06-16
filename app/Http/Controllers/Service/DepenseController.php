<?php
// app/Http/Controllers/Service/DepenseController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Depense;
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

class DepenseController extends Controller
{
    /**
     * Afficher la liste des dépenses
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $query = Depense::where('id_service', $service->id_service)
            ->with(['devise', 'temps']);
        
        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie_depense', $request->categorie);
        }
        
        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('categorie_depense', 'like', "%{$search}%");
            });
        }
        
        $depenses = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques
        $devise = session('devise', 'USD');
        $stats = [
            'total' => Depense::where('id_service', $service->id_service)->count(),
            'total_montant' => Depense::where('id_service', $service->id_service)->sum('montant'),
            'mois_courant' => Depense::where('id_service', $service->id_service)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
            'categories' => Depense::where('id_service', $service->id_service)
                ->select('categorie_depense', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
                ->groupBy('categorie_depense')
                ->get()
        ];
        
        // Liste des catégories pour le filtre
        $categories = Depense::where('id_service', $service->id_service)
            ->distinct('categorie_depense')
            ->pluck('categorie_depense');
        
        return view('service.depenses.index', compact('depenses', 'stats', 'categories', 'service', 'devise'));
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
        $categories = [
            'Fournitures de bureau',
            'Équipement informatique',
            'Logiciels et licences',
            'Communication',
            'Déplacements',
            'Formation',
            'Maintenance',
            'Services externes',
            'Loyer et charges',
            'Autres'
        ];
        
        // Générer une référence automatique
        $reference = $this->generateReference($service->id_service);
        
        return view('service.depenses.create', compact('service', 'devises', 'categories', 'reference'));
    }
    
    /**
     * Enregistrer une nouvelle dépense
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $request->validate([
            'reference' => 'required|string|max:50|unique:depenses,reference',
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'categorie_depense' => 'required|string|max:50',
            'justificatif' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'date_depense' => 'nullable|date',
        ]);
        
        // Créer ou récupérer l'enregistrement temps
        $dateDepense = $request->date_depense ? Carbon::parse($request->date_depense) : now();
        $temps = Temps::firstOrCreate(
            ['date' => $dateDepense->format('Y-m-d')],
            [
                'jour' => $dateDepense->day,
                'mois' => $dateDepense->month,
                'trimestre' => ceil($dateDepense->month / 3),
                'annee' => $dateDepense->year,
                'type_periode' => $dateDepense->isWeekend() ? 'weekend' : 'ouvre',
                'est_cloturee' => false
            ]
        );
        
        // Récupérer la devise
        $devise = Devise::find($request->id_devise);
        
        // Calculer les montants convertis
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        // Créer la dépense
        $depense = Depense::create([
            'reference' => $request->reference,
            'id_service' => $service->id_service,
            'id_utilisateur' => $user->id_utilisateur,
            'id_temps' => $temps->id_temps,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'categorie_depense' => $request->categorie_depense,
            'justificatif' => $request->justificatif,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Enregistrer dans la table des faits financiers
        $indicateurDepense = Indicateur::where('code_kpi', 'DEPENSE')->first();
        $activiteDepense = Activite::where('type_activite', 'depense')->first();
        
        if ($indicateurDepense && $activiteDepense) {
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
                'id_activite' => $activiteDepense->id_activite,
                'id_devise_originale' => $request->id_devise,
                'created_at' => now(),
            ]);
        }
        
        return redirect()->route('service.depenses.index')
            ->with('success', 'Dépense enregistrée avec succès');
    }
    
    /**
     * Afficher les détails d'une dépense
     */
    public function show($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $depense = Depense::with(['devise', 'temps', 'utilisateur'])
            ->where('id_service', $service->id_service)
            ->findOrFail($id);
        
        $devise = session('devise', 'USD');
        
        return view('service.depenses.show', compact('depense', 'service', 'devise'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $depense = Depense::where('id_service', $service->id_service)->findOrFail($id);
        $devises = Devise::all();
        $categories = [
            'Fournitures de bureau',
            'Équipement informatique',
            'Logiciels et licences',
            'Communication',
            'Déplacements',
            'Formation',
            'Maintenance',
            'Services externes',
            'Loyer et charges',
            'Autres'
        ];
        
        return view('service.depenses.edit', compact('depense', 'service', 'devises', 'categories'));
    }
    
    /**
     * Mettre à jour une dépense
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $depense = Depense::where('id_service', $service->id_service)->findOrFail($id);
        
        $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('depenses', 'reference')->ignore($id, 'id_depense')],
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'categorie_depense' => 'required|string|max:50',
            'justificatif' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $depense->update([
            'reference' => $request->reference,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'categorie_depense' => $request->categorie_depense,
            'justificatif' => $request->justificatif,
            'description' => $request->description,
            'updated_at' => now(),
        ]);
        
        // Mettre à jour la table des faits
        $devise = Devise::find($request->id_devise);
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'depense');
        })->where('id_service', $service->id_service)
          ->where('description', $depense->description)
          ->update([
              'montant_original' => $request->montant,
              'montant_cdf' => $montantCDF,
              'montant_usd' => $montantUSD,
              'montant_eur' => $montantEUR,
              'id_devise_originale' => $request->id_devise,
          ]);
        
        return redirect()->route('service.depenses.index')
            ->with('success', 'Dépense modifiée avec succès');
    }
    
    /**
     * Supprimer une dépense
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $depense = Depense::where('id_service', $service->id_service)->findOrFail($id);
        
        // Supprimer l'entrée dans la table des faits
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'depense');
        })->where('id_service', $service->id_service)
          ->where('description', $depense->description)
          ->delete();
        
        $depense->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Dépense supprimée avec succès']);
        }
        
        return redirect()->route('service.depenses.index')
            ->with('success', 'Dépense supprimée avec succès');
    }
    
    /**
     * Exporter les dépenses
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $query = Depense::where('id_service', $service->id_service);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('categorie')) {
            $query->where('categorie_depense', $request->categorie);
        }
        
        $depenses = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'depenses_' . $service->nom_service . '_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Référence', 'Date', 'Montant', 'Devise', 'Catégorie', 'Justificatif', 'Description'], ';');
        
        foreach ($depenses as $depense) {
            fputcsv($handle, [
                $depense->reference,
                $depense->created_at->format('d/m/Y'),
                $depense->montant,
                $depense->devise->code ?? 'USD',
                $depense->categorie_depense,
                $depense->justificatif ?? '-',
                $depense->description ?? '-'
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
        $prefix = 'DEP-' . date('Ymd') . '-';
        $lastDepense = Depense::where('reference', 'like', $prefix . '%')
            ->orderBy('reference', 'desc')
            ->first();
        
        if ($lastDepense) {
            $lastNum = intval(substr($lastDepense->reference, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        
        return $prefix . $newNum;
    }
}