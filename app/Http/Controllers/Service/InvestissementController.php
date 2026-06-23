<?php
// app/Http/Controllers/Service/InvestissementController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Investissement;
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

class InvestissementController extends Controller
{
    /**
     * Afficher la liste des investissements
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $query = Investissement::where('id_service', $service->id_service)
            ->with(['devise', 'temps']);
        
        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('nom_projet', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $investissements = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques
        $devise = session('devise', 'USD');
        
        // 🔥 Évolution des investissements (6 derniers mois)
        $evolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $total = Investissement::where('id_service', $service->id_service)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('montant');
            $evolution[] = $total;
        }
        
        // 🔥 Données des statuts pour le graphique
        $statutsData = Investissement::where('id_service', $service->id_service)
            ->select('statut', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
            ->groupBy('statut')
            ->pluck('montant_total', 'statut')
            ->toArray();
        
        $stats = [
            'total' => Investissement::where('id_service', $service->id_service)->count(),
            'total_montant' => Investissement::where('id_service', $service->id_service)->sum('montant'),
            'en_cours' => Investissement::where('id_service', $service->id_service)->where('statut', 'En cours')->count(),
            'termines' => Investissement::where('id_service', $service->id_service)->where('statut', 'Terminé')->count(),
            'roi_moyen' => Investissement::where('id_service', $service->id_service)->avg('roi_estime') ?? 0,
            'evolution' => $evolution,
            'statuts_data' => $statutsData,
            'statuts' => $statutsData,
        ];
        
        // Liste des statuts pour le filtre
        $statuts = ['En cours', 'Terminé', 'Annulé', 'En attente'];
        
        return view('service.investissements.index', compact('investissements', 'stats', 'statuts', 'service', 'devise'));
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
        $reference = $this->generateReference($service->id_service);
        
        return view('service.investissements.create', compact('service', 'devises', 'reference'));
    }
    
    /**
     * Enregistrer un nouvel investissement
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $request->validate([
            'reference' => 'required|string|max:50|unique:investissements,reference',
            'nom_projet' => 'required|string|max:100',
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'description' => 'nullable|string',
            'duree_mois' => 'required|integer|min:1|max:120',
            'roi_estime' => 'nullable|numeric|min:-100|max:100',
            'statut' => 'required|string|in:En cours,Terminé,Annulé,En attente',
            'date_investissement' => 'nullable|date',
        ]);
        
        $dateInvest = $request->date_investissement ? Carbon::parse($request->date_investissement) : now();
        $temps = Temps::firstOrCreate(
            ['date' => $dateInvest->format('Y-m-d')],
            [
                'jour' => $dateInvest->day,
                'mois' => $dateInvest->month,
                'trimestre' => ceil($dateInvest->month / 3),
                'annee' => $dateInvest->year,
                'type_periode' => $dateInvest->isWeekend() ? 'weekend' : 'ouvre',
                'est_cloturee' => false
            ]
        );
        
        $devise = Devise::find($request->id_devise);
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        $investissement = Investissement::create([
            'reference' => $request->reference,
            'nom_projet' => $request->nom_projet,
            'id_service' => $service->id_service,
            'id_utilisateur' => $user->id_utilisateur,
            'id_temps' => $temps->id_temps,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'description' => $request->description,
            'duree_mois' => $request->duree_mois,
            'roi_estime' => $request->roi_estime,
            'statut' => $request->statut,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $indicateurInvest = Indicateur::where('code_kpi', 'INVEST')->first();
        $activiteInvest = Activite::where('type_activite', 'investissement')->first();
        
        if ($indicateurInvest && $activiteInvest) {
            FinanceFait::create([
                'montant_original' => $request->montant,
                'montant_cdf' => $montantCDF,
                'montant_usd' => $montantUSD,
                'montant_eur' => $montantEUR,
                'description' => $request->description,
                'id_temps' => $temps->id_temps,
                'id_service' => $service->id_service,
                'id_utilisateur' => $user->id_utilisateur,
                'id_indicateur' => $indicateurInvest->id_indicateur,
                'id_activite' => $activiteInvest->id_activite,
                'id_devise_originale' => $request->id_devise,
                'created_at' => now(),
            ]);
        }
        
        return redirect()->route('service.investissements.index')
            ->with('success', 'Investissement enregistré avec succès');
    }
    
    /**
     * Afficher les détails d'un investissement
     */
    public function show($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $investissement = Investissement::with(['devise', 'temps', 'utilisateur'])
            ->where('id_service', $service->id_service)
            ->findOrFail($id);
        
        $devise = session('devise', 'USD');
        $metrics = $this->calculateMetrics($investissement);
        
        return view('service.investissements.show', compact('investissement', 'service', 'devise', 'metrics'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $investissement = Investissement::where('id_service', $service->id_service)->findOrFail($id);
        $devises = Devise::all();
        $statuts = ['En cours', 'Terminé', 'Annulé', 'En attente'];
        
        return view('service.investissements.edit', compact('investissement', 'service', 'devises', 'statuts'));
    }
    
    /**
     * Mettre à jour un investissement
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $investissement = Investissement::where('id_service', $service->id_service)->findOrFail($id);
        
        $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('investissements', 'reference')->ignore($id, 'id_investissement')],
            'nom_projet' => 'required|string|max:100',
            'montant' => 'required|numeric|min:0',
            'id_devise' => 'required|exists:devises,id_devise',
            'description' => 'nullable|string',
            'duree_mois' => 'required|integer|min:1|max:120',
            'roi_estime' => 'nullable|numeric|min:-100|max:100',
            'statut' => 'required|string|in:En cours,Terminé,Annulé,En attente',
        ]);
        
        $investissement->update([
            'reference' => $request->reference,
            'nom_projet' => $request->nom_projet,
            'montant' => $request->montant,
            'id_devise' => $request->id_devise,
            'description' => $request->description,
            'duree_mois' => $request->duree_mois,
            'roi_estime' => $request->roi_estime,
            'statut' => $request->statut,
            'updated_at' => now(),
        ]);
        
        $devise = Devise::find($request->id_devise);
        $montantCDF = $request->montant * ($devise->taux_cdf ?? 1);
        $montantUSD = $request->montant * ($devise->taux_usd ?? 1);
        $montantEUR = $request->montant * ($devise->taux_eur ?? 1);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'investissement');
        })->where('id_service', $service->id_service)
          ->where('description', $investissement->description)
          ->update([
              'montant_original' => $request->montant,
              'montant_cdf' => $montantCDF,
              'montant_usd' => $montantUSD,
              'montant_eur' => $montantEUR,
              'id_devise_originale' => $request->id_devise,
          ]);
        
        return redirect()->route('service.investissements.index')
            ->with('success', 'Investissement modifié avec succès');
    }
    
    /**
     * Supprimer un investissement
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $investissement = Investissement::where('id_service', $service->id_service)->findOrFail($id);
        
        FinanceFait::where('id_activite', function($q) {
            $q->where('type_activite', 'investissement');
        })->where('id_service', $service->id_service)
          ->where('description', $investissement->description)
          ->delete();
        
        $investissement->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Investissement supprimé avec succès']);
        }
        
        return redirect()->route('service.investissements.index')
            ->with('success', 'Investissement supprimé avec succès');
    }
    
    /**
     * Exporter les investissements
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        $query = Investissement::where('id_service', $service->id_service);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $investissements = $query->orderBy('created_at', 'desc')->get();
        
        if ($investissements->isEmpty()) {
            return redirect()->route('service.investissements.index')
                ->with('warning', 'Aucun investissement à exporter');
        }
        
        $filename = 'investissements_' . $service->nom_service . '_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Référence', 'Projet', 'Date', 'Montant', 'Devise', 'Durée', 'ROI', 'Statut', 'Description'], ';');
        
        foreach ($investissements as $invest) {
            fputcsv($handle, [
                $invest->reference,
                $invest->nom_projet,
                $invest->created_at->format('d/m/Y'),
                $invest->montant,
                $invest->devise->code ?? 'USD',
                $invest->duree_mois . ' mois',
                ($invest->roi_estime ?? 0) . '%',
                $invest->statut,
                $invest->description ?? '-'
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
        $prefix = 'INV-' . date('Ymd') . '-';
        $lastInvest = Investissement::where('reference', 'like', $prefix . '%')
            ->orderBy('reference', 'desc')
            ->first();
        
        if ($lastInvest) {
            $lastNum = intval(substr($lastInvest->reference, -4));
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }
        
        return $prefix . $newNum;
    }
    
    /**
     * Calculer des métriques supplémentaires
     */
    private function calculateMetrics($investissement)
    {
        $metrics = [];
        
        $gainMensuelEstime = $investissement->montant * (($investissement->roi_estime ?? 0) / 100) / $investissement->duree_mois;
        $roiAnnualise = $investissement->roi_estime ? ($investissement->roi_estime / $investissement->duree_mois) * 12 : 0;
        
        $performance = 'normal';
        if ($investissement->roi_estime >= 20) {
            $performance = 'excellent';
        } elseif ($investissement->roi_estime >= 10) {
            $performance = 'bon';
        } elseif ($investissement->roi_estime >= 5) {
            $performance = 'moyen';
        } elseif ($investissement->roi_estime < 0) {
            $performance = 'critique';
        }
        
        $metrics = [
            'gain_mensuel_estime' => $gainMensuelEstime,
            'roi_annualise' => round($roiAnnualise, 1),
            'performance' => $performance,
            'retour_sur_investissement_mois' => $investissement->roi_estime > 0 
                ? round($investissement->duree_mois / ($investissement->roi_estime / 100), 1) 
                : 0
        ];
        
        return $metrics;
    }
}