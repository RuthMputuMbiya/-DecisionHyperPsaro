<?php
// app/Http/Controllers/Service/DashboardController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Depense;
use App\Models\Investissement;
use App\Models\Temps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord du service
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $devise = session('devise', 'USD');
        
        // Statistiques du service
        $stats = $this->getServiceStats($service->id_service, $devise);
        
        // Évolution mensuelle
        $monthlyEvolution = $this->getMonthlyEvolution($service->id_service, $devise);
        
        // Indicateurs de performance
        $indicators = $this->getServiceIndicators($service->id_service, $devise);
        
        // Dernières activités
        $recentActivities = $this->getRecentActivities($service->id_service, $devise);
        
        // Top produits (ventes)
        $topProducts = $this->getTopProducts($service->id_service);
        
        // Alertes
        $alerts = $this->getAlerts($service->id_service, $devise);
        
        // Objectifs
        $objectifs = $this->getObjectifs($service->id_service);
        
        // Année en cours
        $currentYear = date('Y');
        
        return view('service.dashboard', compact(
            'service',
            'stats',
            'monthlyEvolution',
            'indicators',
            'recentActivities',
            'topProducts',
            'alerts',
            'objectifs',
            'devise',
            'currentYear'
        ));
    }
    
    /**
     * Afficher les performances détaillées
     */
    public function performances(Request $request)
    {
        $user = auth()->user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé à votre compte');
        }
        
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        
        // Données annuelles
        $annualData = $this->getAnnualData($service->id_service, $devise, $year);
        
        // Comparaison avec année précédente
        $yearComparison = $this->getYearComparison($service->id_service, $devise, $year);
        
        // Performance mensuelle détaillée
        $monthlyDetails = $this->getMonthlyDetails($service->id_service, $devise, $year);
        
        // Top mois
        $topMonths = collect($monthlyDetails)->sortByDesc('ca')->take(3);
        
        return view('service.performances', compact(
            'service',
            'annualData',
            'yearComparison',
            'monthlyDetails',
            'topMonths',
            'devise',
            'year'
        ));
    }
    
    /**
     * Obtenir les statistiques du service
     */
    private function getServiceStats($serviceId, $devise)
    {
        $currentYear = date('Y');
        $lastMonth = date('m') - 1;
        $lastYear = $lastMonth < 1 ? date('Y') - 1 : $currentYear;
        $lastMonthNum = $lastMonth < 1 ? 12 : $lastMonth;
        
        // CA total
        $caTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        // CA mois en cours
        $caMois = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear)->where('mois', date('m'));
            })
            ->sum("montant_{$devise}");
        
        // CA mois précédent
        $caMoisPrecedent = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($lastYear, $lastMonthNum) {
                $q->where('annee', $lastYear)->where('mois', $lastMonthNum);
            })
            ->sum("montant_{$devise}");
        
        // Marge
        $marge = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        // Dépenses
        $depenses = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        // Investissements
        $investissements = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->where('type_activite', 'investissement'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        // Taux de marge
        $tauxMarge = $caTotal > 0 ? round(($marge / $caTotal) * 100, 1) : 0;
        
        // Croissance mensuelle
        $croissanceMensuelle = $caMoisPrecedent > 0 
            ? round(($caMois - $caMoisPrecedent) / $caMoisPrecedent * 100, 1) 
            : 0;
        
        // Nombre de transactions
        $transactions = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count() + 
            Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count() +
            Depense::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count();
        
        return [
            'ca_total' => $caTotal,
            'ca_mois' => $caMois,
            'ca_mois_precedent' => $caMoisPrecedent,
            'croissance_mensuelle' => $croissanceMensuelle,
            'marge' => $marge,
            'taux_marge' => $tauxMarge,
            'depenses' => $depenses,
            'investissements' => $investissements,
            'transactions' => $transactions
        ];
    }
    
    /**
     * Obtenir l'évolution mensuelle
     */
    private function getMonthlyEvolution($serviceId, $devise)
    {
        $currentYear = date('Y');
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $ca = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($i, $currentYear) {
                    $q->where('mois', $i)->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}");
            
            $data[] = [
                'month' => Carbon::createFromDate($currentYear, $i, 1)->translatedFormat('M'),
                'ca' => $ca
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtenir les indicateurs du service
     */
    private function getServiceIndicators($serviceId, $devise)
    {
        $currentYear = date('Y');
        
        // Objectif CA (simulation: +10% par rapport à l'année dernière)
        $lastYearCA = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear - 1);
            })
            ->sum("montant_{$devise}");
        
        $objectifCA = $lastYearCA * 1.1;
        $caActuel = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        $progressionObjectif = $objectifCA > 0 ? round(($caActuel / $objectifCA) * 100, 1) : 0;
        
        // Budget consommé
        $service = \App\Models\Service::find($serviceId);
        $budgetConsomme = $service ? round(($caActuel / $service->budget_annuel) * 100, 1) : 0;
        
        // Performance vs moyenne service
        $allServicesAvg = FinanceFait::whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->avg("montant_{$devise}");
        
        $performanceVsMoyenne = $allServicesAvg > 0 
            ? round(($caActuel / $allServicesAvg) * 100, 1) 
            : 0;
        
        return [
            'objectif_ca' => $objectifCA,
            'progression_objectif' => $progressionObjectif,
            'budget_consomme' => $budgetConsomme,
            'performance_vs_moyenne' => $performanceVsMoyenne,
            'ca_actuel' => $caActuel
        ];
    }
    
    /**
     * Obtenir les dernières activités
     */
    private function getRecentActivities($serviceId, $devise)
    {
        $activites = [];
        
        // Dernières ventes
        $ventes = Vente::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($v) use ($devise) {
                return [
                    'type' => 'vente',
                    'reference' => $v->reference,
                    'montant' => $v->montant,
                    'client' => $v->client,
                    'date' => $v->created_at,
                    'icon' => 'fa-shopping-cart',
                    'color' => '#0e9f6e'
                ];
            });
        
        // Derniers achats
        $achats = Achat::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($a) use ($devise) {
                return [
                    'type' => 'achat',
                    'reference' => $a->reference,
                    'montant' => $a->montant,
                    'fournisseur' => $a->fournisseur,
                    'date' => $a->created_at,
                    'icon' => 'fa-truck',
                    'color' => '#f5a623'
                ];
            });
        
        // Dernières dépenses
        $depenses = Depense::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($d) use ($devise) {
                return [
                    'type' => 'depense',
                    'reference' => $d->reference,
                    'montant' => $d->montant,
                    'categorie' => $d->categorie_depense,
                    'date' => $d->created_at,
                    'icon' => 'fa-receipt',
                    'color' => '#e02424'
                ];
            });
        
        // Fusionner et trier
        $activites = $ventes->merge($achats)->merge($depenses);
        $activites = $activites->sortByDesc('date')->take(10);
        
        return $activites;
    }
    
    /**
     * Obtenir les top produits
     */
    private function getTopProducts($serviceId)
    {
        // Simuler des top produits (à adapter selon votre structure)
        return [
            ['name' => 'Produit A', 'ventes' => 12500, 'evolution' => '+15%'],
            ['name' => 'Produit B', 'ventes' => 9800, 'evolution' => '+8%'],
            ['name' => 'Produit C', 'ventes' => 7600, 'evolution' => '-2%'],
            ['name' => 'Produit D', 'ventes' => 5400, 'evolution' => '+22%'],
            ['name' => 'Produit E', 'ventes' => 3200, 'evolution' => '+5%'],
        ];
    }
    
    /**
     * Obtenir les alertes
     */
    private function getAlerts($serviceId, $devise)
    {
        $alerts = [];
        $currentYear = date('Y');
        
        // CA en baisse
        $caLast3Months = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear)->where('mois', '>=', date('m') - 2);
            })
            ->sum("montant_{$devise}");
        
        $caPrevious3Months = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear)->where('mois', '<', date('m') - 2)->where('mois', '>=', date('m') - 5);
            })
            ->sum("montant_{$devise}");
        
        if ($caPrevious3Months > 0 && $caLast3Months < $caPrevious3Months * 0.9) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Baisse du chiffre d\'affaires',
                'message' => 'Le CA a baissé de plus de 10% sur les 3 derniers mois',
                'icon' => 'fa-chart-line'
            ];
        }
        
        // Dépenses en hausse
        $depensesLastMonth = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear)->where('mois', date('m'));
            })
            ->sum("montant_{$devise}");
        
        $depensesPrevMonth = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear)->where('mois', date('m') - 1);
            })
            ->sum("montant_{$devise}");
        
        if ($depensesPrevMonth > 0 && $depensesLastMonth > $depensesPrevMonth * 1.2) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Hausse des dépenses',
                'message' => 'Les dépenses ont augmenté de plus de 20% par rapport au mois dernier',
                'icon' => 'fa-receipt'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Obtenir les objectifs
     */
    private function getObjectifs($serviceId)
    {
        $currentYear = date('Y');
        
        $caActuel = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum('montant_usd');
        
        $lastYearCA = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear - 1);
            })
            ->sum('montant_usd');
        
        return [
            'ca_annuel' => [
                'objectif' => $lastYearCA * 1.1,
                'actuel' => $caActuel,
                'unite' => 'USD'
            ],
            'taux_marge' => [
                'objectif' => 25,
                'actuel' => $caActuel > 0 ? round(($caActuel * 0.2), 1) : 0,
                'unite' => '%'
            ],
            'satisfaction' => [
                'objectif' => 90,
                'actuel' => 85,
                'unite' => '%'
            ]
        ];
    }
    
    /**
     * Obtenir les données annuelles
     */
    private function getAnnualData($serviceId, $devise, $year)
    {
        $caTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}");
        
        $margeTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}");
        
        $depensesTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}");
        
        return [
            'ca' => $caTotal,
            'marge' => $margeTotal,
            'depenses' => $depensesTotal,
            'taux_marge' => $caTotal > 0 ? round(($margeTotal / $caTotal) * 100, 1) : 0
        ];
    }
    
    /**
     * Comparaison année
     */
    private function getYearComparison($serviceId, $devise, $year)
    {
        $currentYearData = $this->getAnnualData($serviceId, $devise, $year);
        $prevYearData = $this->getAnnualData($serviceId, $devise, $year - 1);
        
        $croissance = $prevYearData['ca'] > 0 
            ? round(($currentYearData['ca'] - $prevYearData['ca']) / $prevYearData['ca'] * 100, 1) 
            : 0;
        
        return [
            'current' => $currentYearData,
            'previous' => $prevYearData,
            'croissance' => $croissance,
            'evolution' => $croissance >= 0 ? 'up' : 'down'
        ];
    }
    
    /**
     * Détails mensuels
     */
    private function getMonthlyDetails($serviceId, $devise, $year)
    {
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $ca = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($i, $year) {
                    $q->where('mois', $i)->where('annee', $year);
                })
                ->sum("montant_{$devise}");
            
            $marge = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
                ->whereHas('temps', function($q) use ($i, $year) {
                    $q->where('mois', $i)->where('annee', $year);
                })
                ->sum("montant_{$devise}");
            
            $data[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->translatedFormat('F'),
                'ca' => $ca,
                'marge' => $marge,
                'taux_marge' => $ca > 0 ? round(($marge / $ca) * 100, 1) : 0
            ];
        }
        
        return $data;
    }
}