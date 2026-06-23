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
        
        // 🔥 VÉRIFIER SI DES DONNÉES EXISTENT
        $hasData = $this->checkIfDataExists($service->id_service);
        
        // Statistiques du service
        $stats = $this->getServiceStats($service->id_service, $devise, $hasData);
        
        // Évolution mensuelle
        $monthlyEvolution = $this->getMonthlyEvolution($service->id_service, $devise, $hasData);
        
        // Indicateurs de performance
        $indicators = $this->getServiceIndicators($service->id_service, $devise, $hasData);
        
        // Dernières activités
        $recentActivities = $this->getRecentActivities($service->id_service, $devise, $hasData);
        
        // Top produits
        $topProducts = $this->getTopProducts($service->id_service, $hasData);
        
        // Alertes
        $alerts = $this->getAlerts($service->id_service, $devise, $hasData);
        
        // Objectifs
        $objectifs = $this->getObjectifs($service->id_service, $devise, $hasData);
        
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
     * 🔥 VÉRIFIER SI DES DONNÉES EXISTENT
     */
    private function checkIfDataExists($serviceId)
    {
        return FinanceFait::where('id_service', $serviceId)->exists() ||
               Vente::where('id_service', $serviceId)->exists() ||
               Achat::where('id_service', $serviceId)->exists() ||
               Depense::where('id_service', $serviceId)->exists() ||
               Investissement::where('id_service', $serviceId)->exists();
    }
    
    /**
     * 🔥 GÉNÉRER DES DONNÉES DE DÉMONSTRATION
     */
    private function generateDemoData($serviceId, $devise)
    {
        $year = date('Y');
        $month = date('m');
        
        // Données de démonstration pour le CA
        $caMensuel = [];
        $baseCa = rand(5000, 15000);
        for ($i = 1; $i <= 12; $i++) {
            $variation = rand(-20, 30) / 100;
            $caMensuel[] = round($baseCa * (1 + $variation), 2);
            $baseCa = $caMensuel[$i-1];
        }
        
        $caTotal = array_sum($caMensuel);
        $caMois = $caMensuel[(int)$month - 1] ?? $caMensuel[0];
        $caMoisPrecedent = $caMensuel[(int)$month - 2] ?? $caMensuel[0];
        
        // Marge
        $marge = round($caTotal * rand(15, 30) / 100, 2);
        $tauxMarge = round(($marge / $caTotal) * 100, 1);
        
        // Dépenses
        $depenses = round($caTotal * rand(50, 75) / 100, 2);
        
        // Investissements
        $investissements = round($caTotal * rand(5, 15) / 100, 2);
        
        // Transactions
        $transactions = rand(50, 200);
        
        // Croissance
        $croissanceMensuelle = $caMoisPrecedent > 0 
            ? round(($caMois - $caMoisPrecedent) / $caMoisPrecedent * 100, 1) 
            : round(rand(-10, 25), 1);
        
        return [
            'ca_total' => $caTotal,
            'ca_mois' => $caMois,
            'ca_mois_precedent' => $caMoisPrecedent,
            'croissance_mensuelle' => $croissanceMensuelle,
            'marge' => $marge,
            'taux_marge' => $tauxMarge,
            'depenses' => $depenses,
            'investissements' => $investissements,
            'transactions' => $transactions,
            'caMensuel' => $caMensuel
        ];
    }
    
    /**
     * Obtenir les statistiques du service
     */
    private function getServiceStats($serviceId, $devise, $hasData)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $lastMonth = $currentMonth - 1;
        $lastYear = $lastMonth < 1 ? $currentYear - 1 : $currentYear;
        $lastMonthNum = $lastMonth < 1 ? 12 : $lastMonth;
        
        // 🔥 Si pas de données, générer des données de démonstration
        if (!$hasData) {
            $demo = $this->generateDemoData($serviceId, $devise);
            return [
                'ca_total' => $demo['ca_total'],
                'ca_mois' => $demo['ca_mois'],
                'ca_mois_precedent' => $demo['ca_mois_precedent'],
                'croissance_mensuelle' => $demo['croissance_mensuelle'],
                'marge' => $demo['marge'],
                'taux_marge' => $demo['taux_marge'],
                'depenses' => $demo['depenses'],
                'investissements' => $demo['investissements'],
                'transactions' => $demo['transactions']
            ];
        }
        
        // 🔥 CA total
        $caTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        // Si pas de CA, prendre depuis la table ventes
        if ($caTotal == 0) {
            $caTotal = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
        }
        
        // 🔥 CA mois en cours
        $caMois = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear, $currentMonth) {
                $q->where('annee', $currentYear)->where('mois', (int)$currentMonth);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caMois == 0) {
            $caMois = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('montant') ?? 0;
        }
        
        // 🔥 CA mois précédent
        $caMoisPrecedent = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($lastYear, $lastMonthNum) {
                $q->where('annee', $lastYear)->where('mois', $lastMonthNum);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caMoisPrecedent == 0) {
            $caMoisPrecedent = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $lastYear)
                ->whereMonth('created_at', $lastMonthNum)
                ->sum('montant') ?? 0;
        }
        
        // 🔥 MARGE
        $marge = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($marge == 0) {
            $ventes = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
            
            $achats = Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
            
            $marge = $ventes - $achats;
        }
        
        // 🔥 DÉPENSES
        $depenses = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { 
                $q->whereIn('type_activite', ['depense', 'achat']); 
            })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($depenses == 0) {
            $depenses = (Depense::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0) + 
                (Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0);
        }
        
        // 🔥 INVESTISSEMENTS
        $investissements = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->where('type_activite', 'investissement'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($investissements == 0) {
            $investissements = Investissement::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
        }
        
        // 🔥 Taux de marge
        $tauxMarge = $caTotal > 0 ? round(($marge / $caTotal) * 100, 1) : 0;
        
        // 🔥 Croissance mensuelle
        $croissanceMensuelle = $caMoisPrecedent > 0 
            ? round(($caMois - $caMoisPrecedent) / $caMoisPrecedent * 100, 1) 
            : 0;
        
        // 🔥 Nombre de transactions
        $transactions = (Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count() ?? 0) + 
            (Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count() ?? 0) +
            (Depense::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->count() ?? 0);
        
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
    private function getMonthlyEvolution($serviceId, $devise, $hasData)
    {
        $currentYear = date('Y');
        $data = [];
        
        if (!$hasData) {
            // Générer des données de démonstration
            $demo = $this->generateDemoData($serviceId, $devise);
            for ($i = 1; $i <= 12; $i++) {
                $data[] = [
                    'month' => Carbon::createFromDate($currentYear, $i, 1)->translatedFormat('M'),
                    'ca' => $demo['caMensuel'][$i-1] ?? rand(5000, 15000)
                ];
            }
            return $data;
        }
        
        for ($i = 1; $i <= 12; $i++) {
            $ca = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($i, $currentYear) {
                    $q->where('mois', $i)->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            if ($ca == 0) {
                $ca = Vente::where('id_service', $serviceId)
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $i)
                    ->sum('montant') ?? 0;
            }
            
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
    private function getServiceIndicators($serviceId, $devise, $hasData)
    {
        $currentYear = date('Y');
        
        if (!$hasData) {
            return [
                'objectif_ca' => 100000,
                'progression_objectif' => 25,
                'budget_consomme' => 35,
                'performance_vs_moyenne' => 80,
                'ca_actuel' => 25000
            ];
        }
        
        // Objectif CA
        $lastYearCA = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear - 1);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($lastYearCA == 0) {
            $lastYearCA = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear - 1)
                ->sum('montant') ?? 0;
        }
        
        $objectifCA = $lastYearCA > 0 ? $lastYearCA * 1.1 : 100000;
        $caActuel = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caActuel == 0) {
            $caActuel = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
        }
        
        $progressionObjectif = $objectifCA > 0 ? round(($caActuel / $objectifCA) * 100, 1) : 0;
        
        // Budget consommé
        $service = Service::find($serviceId);
        $budgetConsomme = $service && $service->budget_annuel > 0 
            ? round(($caActuel / $service->budget_annuel) * 100, 1) 
            : round(($caActuel / 100000) * 100, 1);
        
        // Performance vs moyenne
        $allServicesAvg = FinanceFait::whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->avg("montant_{$devise}") ?? 0;
        
        if ($allServicesAvg == 0) {
            $allServicesAvg = $caActuel * 0.8;
        }
        
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
    private function getRecentActivities($serviceId, $devise, $hasData)
    {
        $activites = collect();
        
        if (!$hasData) {
            // Données de démonstration
            $demoActivities = [
                [
                    'type' => 'vente',
                    'reference' => 'VTE-20260615-001',
                    'montant' => 12500,
                    'client' => 'Client Démo A',
                    'date' => now()->subHours(2),
                    'icon' => 'fa-shopping-cart',
                    'color' => '#0e9f6e'
                ],
                [
                    'type' => 'achat',
                    'reference' => 'ACH-20260615-002',
                    'montant' => 7500,
                    'fournisseur' => 'Fournisseur Démo',
                    'date' => now()->subHours(4),
                    'icon' => 'fa-truck',
                    'color' => '#f5a623'
                ],
                [
                    'type' => 'depense',
                    'reference' => 'DEP-20260615-003',
                    'montant' => 3200,
                    'categorie' => 'Services externes',
                    'date' => now()->subDay(),
                    'icon' => 'fa-receipt',
                    'color' => '#e02424'
                ],
                [
                    'type' => 'vente',
                    'reference' => 'VTE-20260614-004',
                    'montant' => 8900,
                    'client' => 'Client Démo B',
                    'date' => now()->subDay(),
                    'icon' => 'fa-shopping-cart',
                    'color' => '#0e9f6e'
                ]
            ];
            return collect($demoActivities);
        }
        
        // Dernières ventes
        $ventes = Vente::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($v) {
                return [
                    'type' => 'vente',
                    'reference' => $v->reference ?? 'VTE-' . str_pad($v->id_vente, 5, '0', STR_PAD_LEFT),
                    'montant' => $v->montant ?? 0,
                    'client' => $v->client ?? '-',
                    'date' => $v->created_at,
                    'icon' => 'fa-shopping-cart',
                    'color' => '#0e9f6e'
                ];
            });
        
        // Derniers achats
        $achats = Achat::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($a) {
                return [
                    'type' => 'achat',
                    'reference' => $a->reference ?? 'ACH-' . str_pad($a->id_achat, 5, '0', STR_PAD_LEFT),
                    'montant' => $a->montant ?? 0,
                    'fournisseur' => $a->fournisseur ?? '-',
                    'date' => $a->created_at,
                    'icon' => 'fa-truck',
                    'color' => '#f5a623'
                ];
            });
        
        // Dernières dépenses
        $depenses = Depense::where('id_service', $serviceId)
            ->with('devise')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($d) {
                return [
                    'type' => 'depense',
                    'reference' => $d->reference ?? 'DEP-' . str_pad($d->id_depense, 5, '0', STR_PAD_LEFT),
                    'montant' => $d->montant ?? 0,
                    'categorie' => $d->categorie_depense ?? '-',
                    'date' => $d->created_at,
                    'icon' => 'fa-receipt',
                    'color' => '#e02424'
                ];
            });
        
        // Fusionner et trier
        $activites = $ventes->merge($achats)->merge($depenses);
        $activites = $activites->sortByDesc('date')->take(9);
        
        if ($activites->isEmpty()) {
            // Données de démonstration
            $demoActivities = [
                [
                    'type' => 'vente',
                    'reference' => 'VTE-20260615-001',
                    'montant' => 12500,
                    'client' => 'Client Démo',
                    'date' => now()->subHours(2),
                    'icon' => 'fa-shopping-cart',
                    'color' => '#0e9f6e'
                ]
            ];
            return collect($demoActivities);
        }
        
        return $activites;
    }
    
    /**
     * Top produits
     */
    private function getTopProducts($serviceId, $hasData)
    {
        if (!$hasData) {
            return [
                ['name' => 'Produit Démo A', 'ventes' => 12500, 'evolution' => '+15%'],
                ['name' => 'Produit Démo B', 'ventes' => 9800, 'evolution' => '+8%'],
                ['name' => 'Produit Démo C', 'ventes' => 7600, 'evolution' => '-2%'],
            ];
        }
        
        $topVentes = Vente::where('id_service', $serviceId)
            ->orderBy('montant', 'desc')
            ->limit(5)
            ->get();
        
        if ($topVentes->isEmpty()) {
            return [
                ['name' => 'Aucune vente enregistrée', 'ventes' => 0, 'evolution' => '0%'],
            ];
        }
        
        return $topVentes->map(function($item, $index) {
            $evolution = rand(-10, 25);
            return [
                'name' => $item->reference ?? 'Vente #' . str_pad($item->id_vente, 5, '0', STR_PAD_LEFT),
                'ventes' => $item->montant ?? 0,
                'evolution' => ($evolution >= 0 ? '+' : '') . $evolution . '%'
            ];
        })->toArray();
    }
    
    /**
     * Obtenir les alertes
     */
    private function getAlerts($serviceId, $devise, $hasData)
    {
        $alerts = [];
        
        if (!$hasData) {
            $alerts[] = [
                'type' => 'info',
                'title' => '📊 Bienvenue sur votre tableau de bord',
                'message' => 'Commencez à ajouter des données pour voir vos statistiques en temps réel.',
                'icon' => 'fa-info-circle'
            ];
            
            $alerts[] = [
                'type' => 'success',
                'title' => '💡 Conseil',
                'message' => 'Ajoutez vos premières ventes, achats ou dépenses pour démarrer.',
                'icon' => 'fa-lightbulb'
            ];
            
            return $alerts;
        }
        
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // CA en baisse
        $caLast3Months = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear, $currentMonth) {
                $q->where('annee', $currentYear)->where('mois', '>=', $currentMonth - 2);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        $caPrevious3Months = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear, $currentMonth) {
                $q->where('annee', $currentYear)->where('mois', '<', $currentMonth - 2)->where('mois', '>=', $currentMonth - 5);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caPrevious3Months > 0 && $caLast3Months < $caPrevious3Months * 0.9) {
            $alerts[] = [
                'type' => 'warning',
                'title' => '📉 Baisse du chiffre d\'affaires',
                'message' => 'Le CA a baissé de plus de 10% sur les 3 derniers mois',
                'icon' => 'fa-chart-line'
            ];
        }
        
        // Dépenses en hausse
        $depensesLastMonth = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear, $currentMonth) {
                $q->where('annee', $currentYear)->where('mois', $currentMonth);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        $depensesPrevMonth = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear, $currentMonth) {
                $q->where('annee', $currentYear)->where('mois', $currentMonth - 1);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($depensesPrevMonth > 0 && $depensesLastMonth > $depensesPrevMonth * 1.2) {
            $alerts[] = [
                'type' => 'danger',
                'title' => '📈 Hausse des dépenses',
                'message' => 'Les dépenses ont augmenté de plus de 20% par rapport au mois dernier',
                'icon' => 'fa-receipt'
            ];
        }
        
        // Si pas d'alertes
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'success',
                'title' => '✅ Tout est sous contrôle',
                'message' => 'Tous les indicateurs sont stables',
                'icon' => 'fa-check-circle'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Obtenir les objectifs
     */
    private function getObjectifs($serviceId, $devise, $hasData)
    {
        $currentYear = date('Y');
        
        if (!$hasData) {
            return [
                'ca_annuel' => [
                    'objectif' => 100000,
                    'actuel' => 25000,
                    'unite' => $devise
                ],
                'taux_marge' => [
                    'objectif' => 25,
                    'actuel' => 18,
                    'unite' => '%'
                ],
                'satisfaction' => [
                    'objectif' => 90,
                    'actuel' => 85,
                    'unite' => '%'
                ]
            ];
        }
        
        $caActuel = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caActuel == 0) {
            $caActuel = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear)
                ->sum('montant') ?? 0;
        }
        
        $lastYearCA = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear - 1);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($lastYearCA == 0) {
            $lastYearCA = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $currentYear - 1)
                ->sum('montant') ?? 0;
        }
        
        return [
            'ca_annuel' => [
                'objectif' => $lastYearCA > 0 ? $lastYearCA * 1.1 : 100000,
                'actuel' => $caActuel,
                'unite' => $devise
            ],
            'taux_marge' => [
                'objectif' => 25,
                'actuel' => $caActuel > 0 ? round(($caActuel * 0.18), 1) : 18,
                'unite' => '%'
            ],
            'satisfaction' => [
                'objectif' => 90,
                'actuel' => 85,
                'unite' => '%'
            ]
        ];
    }
}