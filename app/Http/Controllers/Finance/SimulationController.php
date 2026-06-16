<?php
// app/Http/Controllers/Finance/SimulationController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Simulation;
use App\Models\Decision;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimulationController extends Controller
{
    /**
     * Afficher la liste des simulations
     */
    public function index(Request $request)
    {
        $query = Simulation::with('utilisateur', 'decision');
        
        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_simulation', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filtre par date
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case 'year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
            }
        }
        
        $simulations = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques
        $stats = [
            'total' => Simulation::count(),
            'sauvegardees' => Simulation::where('est_sauvegardee', true)->count(),
            'avec_decision' => Simulation::whereNotNull('id_decision')->count(),
            'moyenne_impact' => Simulation::avg(DB::raw('JSON_EXTRACT(resultats, "$.impact_tresorerie")')) ?? 0,
        ];
        
        return view('finance.simulations.index', compact('simulations', 'stats'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $services = Service::where('est_actif', true)->get();
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        // Données actuelles pour la simulation
        $currentData = $this->getCurrentFinancialData();
        
        return view('finance.simulations.create', compact('services', 'indicateurs', 'currentData'));
    }
    
    /**
     * Enregistrer une nouvelle simulation
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_simulation' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parametres_saisis' => 'required|array',
            'resultats' => 'required|array',
        ]);
        
        $simulation = Simulation::create([
            'nom_simulation' => $request->nom_simulation,
            'description' => $request->description,
            'parametres_saisis' => $request->parametres_saisis,
            'resultats' => $request->resultats,
            'id_utilisateur' => auth()->id(),
            'est_sauvegardee' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Si une décision est liée
        if ($request->has('creer_decision') && $request->creer_decision) {
            $decision = Decision::create([
                'titre' => 'Décision basée sur : ' . $request->nom_simulation,
                'description' => $request->description,
                'parametres' => $request->parametres_saisis,
                'resultat_simule' => $request->resultats['ca_apres'] ?? 0,
                'impact_tresorerie' => $request->resultats['impact_tresorerie'] ?? 0,
                'decision_prise' => $request->decision_prise ?? 'En attente de validation',
                'cree_par' => auth()->id(),
                'statut' => 'En attente',
                'created_at' => now(),
            ]);
            
            $simulation->update(['id_decision' => $decision->id_decision]);
        }
        
        return redirect()->route('finance.simulations.show', $simulation->id_simulation)
            ->with('success', 'Simulation créée avec succès');
    }
    
    /**
     * Afficher les détails d'une simulation
     */
    public function show($id)
    {
        $simulation = Simulation::with(['utilisateur', 'decision'])->findOrFail($id);
        
        // Décoder les paramètres et résultats
        $parametres = is_string($simulation->parametres_saisis) 
            ? json_decode($simulation->parametres_saisis, true) 
            : $simulation->parametres_saisis;
        
        $resultats = is_string($simulation->resultats) 
            ? json_decode($simulation->resultats, true) 
            : $simulation->resultats;
        
        // Calcul des métriques supplémentaires
        $metrics = $this->calculateMetrics($parametres, $resultats);
        
        return view('finance.simulations.show', compact('simulation', 'parametres', 'resultats', 'metrics'));
    }
    
    /**
     * Afficher l'historique des simulations
     */
    public function history(Request $request)
    {
        $query = Simulation::with('utilisateur', 'decision')
            ->orderBy('created_at', 'desc');
        
        // Filtre par utilisateur
        if ($request->filled('user_id')) {
            $query->where('id_utilisateur', $request->user_id);
        }
        
        // Filtre par période
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month)
                  ->whereYear('created_at', $request->year ?? date('Y'));
        }
        
        $simulations = $query->paginate(15);
        
        // Statistiques par mois
        $monthlyStats = Simulation::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        $users = \App\Models\Utilisateur::where('est_actif', true)->get();
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.simulations.history', compact('simulations', 'monthlyStats', 'users', 'years'));
    }
    
    /**
     * Calculer une simulation en temps réel (AJAX)
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'ca_variation' => 'nullable|numeric|min:-100|max:100',
            'depenses_variation' => 'nullable|numeric|min:-100|max:100',
            'investissement' => 'nullable|numeric|min:0',
            'periode' => 'nullable|integer|min:1|max:36',
            'service_id' => 'nullable|exists:services,id_service',
        ]);
        
        $devise = session('devise', 'USD');
        $currentData = $this->getCurrentFinancialData($request->service_id);
        
        // Calcul des variations
        $caApres = $currentData['ca_actuel'] * (1 + ($request->ca_variation ?? 0) / 100);
        $depensesApres = $currentData['depenses_actuelles'] * (1 + ($request->depenses_variation ?? 0) / 100);
        $investissement = $request->investissement ?? 0;
        $periode = $request->periode ?? 12;
        
        // Calcul des nouveaux indicateurs
        $margeApres = $caApres - $depensesApres;
        $tauxMargeApres = $caApres > 0 ? ($margeApres / $caApres) * 100 : 0;
        
        // Impact sur trésorerie
        $impactTresorerie = ($caApres - $currentData['ca_actuel']) - ($depensesApres - $currentData['depenses_actuelles']) - $investissement;
        
        // ROI estimé
        $roi = $investissement > 0 ? ($impactTresorerie / $investissement) * 100 : 0;
        
        // Délai de retour sur investissement (mois)
        $retourInvestissement = $impactTresorerie > 0 ? round($investissement / ($impactTresorerie / $periode), 1) : 0;
        
        // Seuils d'alerte
        $alertes = [];
        if ($tauxMargeApres < 15) {
            $alertes[] = ['type' => 'warning', 'message' => 'Taux de marge faible (<15%)'];
        }
        if ($roi < 10 && $investissement > 0) {
            $alertes[] = ['type' => 'warning', 'message' => 'ROI estimé faible (<10%)'];
        }
        if ($retourInvestissement > 24 && $investissement > 0) {
            $alertes[] = ['type' => 'info', 'message' => 'Délai de retour sur investissement > 24 mois'];
        }
        
        $resultats = [
            'ca_actuel' => $currentData['ca_actuel'],
            'ca_apres' => $caApres,
            'depenses_actuelles' => $currentData['depenses_actuelles'],
            'depenses_apres' => $depensesApres,
            'marge_actuelle' => $currentData['marge_actuelle'],
            'marge_apres' => $margeApres,
            'taux_marge_actuel' => $currentData['taux_marge_actuel'],
            'taux_marge_apres' => $tauxMargeApres,
            'investissement' => $investissement,
            'impact_tresorerie' => $impactTresorerie,
            'roi' => round($roi, 1),
            'retour_investissement' => $retourInvestissement,
            'alertes' => $alertes,
            'periodes' => $periode,
            'devise' => $devise
        ];
        
        return response()->json([
            'success' => true,
            'resultats' => $resultats
        ]);
    }
    
    /**
     * Sauvegarder une simulation (AJAX)
     */
    public function saveSimulation(Request $request)
    {
        $request->validate([
            'nom_simulation' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parametres' => 'required|array',
            'resultats' => 'required|array',
        ]);
        
        $simulation = Simulation::create([
            'nom_simulation' => $request->nom_simulation,
            'description' => $request->description,
            'parametres_saisis' => $request->parametres,
            'resultats' => $request->resultats,
            'id_utilisateur' => auth()->id(),
            'est_sauvegardee' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Simulation sauvegardée avec succès',
            'simulation_id' => $simulation->id_simulation
        ]);
    }
    
    /**
     * Supprimer une simulation
     */
    public function destroy($id)
    {
        $simulation = Simulation::findOrFail($id);
        
        // Vérifier si la simulation est liée à une décision
        if ($simulation->id_decision) {
            return back()->with('error', 'Cette simulation est liée à une décision et ne peut pas être supprimée');
        }
        
        $simulation->delete();
        
        return redirect()->route('finance.simulations.index')
            ->with('success', 'Simulation supprimée avec succès');
    }
    
    /**
     * Dupliquer une simulation
     */
    public function duplicate($id)
    {
        $original = Simulation::findOrFail($id);
        
        $newSimulation = $original->replicate();
        $newSimulation->nom_simulation = $original->nom_simulation . ' (copie)';
        $newSimulation->created_at = now();
        $newSimulation->updated_at = now();
        $newSimulation->save();
        
        return redirect()->route('finance.simulations.show', $newSimulation->id_simulation)
            ->with('success', 'Simulation dupliquée avec succès');
    }
    
    /**
     * Exporter l'historique
     */
    public function exportHistory(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $simulations = Simulation::with('utilisateur')
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($format == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.simulations.exports.history', compact('simulations'));
            return $pdf->download('historique_simulations_' . date('Y-m-d') . '.pdf');
        }
        
        // Export Excel
        return \Maatwebsite\Excel\Facades\Excel::download(new SimulationsExport($simulations), 'simulations_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Obtenir les données financières actuelles
     */
    private function getCurrentFinancialData($serviceId = null)
    {
        $devise = session('devise', 'USD');
        $currentYear = date('Y');
        
        $queryCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        });
        
        $queryDepenses = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        });
        
        if ($serviceId) {
            $queryCA->where('id_service', $serviceId);
            $queryDepenses->where('id_service', $serviceId);
        }
        
        $caActuel = $queryCA->sum("montant_{$devise}");
        $depensesActuelles = $queryDepenses->sum("montant_{$devise}");
        $margeActuelle = $caActuel - $depensesActuelles;
        $tauxMargeActuel = $caActuel > 0 ? ($margeActuelle / $caActuel) * 100 : 0;
        
        return [
            'ca_actuel' => $caActuel,
            'depenses_actuelles' => $depensesActuelles,
            'marge_actuelle' => $margeActuelle,
            'taux_marge_actuel' => round($tauxMargeActuel, 1),
            'devise' => $devise
        ];
    }
    
    /**
     * Calculer des métriques supplémentaires
     */
    private function calculateMetrics($parametres, $resultats)
    {
        $metrics = [];
        
        // Variation CA
        if (isset($resultats['ca_actuel']) && isset($resultats['ca_apres'])) {
            $metrics['ca_variation'] = [
                'value' => $resultats['ca_apres'] - $resultats['ca_actuel'],
                'percentage' => $resultats['ca_actuel'] > 0 
                    ? round(($resultats['ca_apres'] - $resultats['ca_actuel']) / $resultats['ca_actuel'] * 100, 1) 
                    : 0
            ];
        }
        
        // Variation marge
        if (isset($resultats['marge_actuelle']) && isset($resultats['marge_apres'])) {
            $metrics['marge_variation'] = [
                'value' => $resultats['marge_apres'] - $resultats['marge_actuelle'],
                'percentage' => $resultats['marge_actuelle'] > 0 
                    ? round(($resultats['marge_apres'] - $resultats['marge_actuelle']) / $resultats['marge_actuelle'] * 100, 1) 
                    : 0
            ];
        }
        
        // Seuils
        $metrics['seuils'] = [
            'ca_objectif' => $resultats['ca_actuel'] * 1.1,
            'marge_objectif' => $resultats['marge_actuelle'] * 1.05
        ];
        
        return $metrics;
    }
}