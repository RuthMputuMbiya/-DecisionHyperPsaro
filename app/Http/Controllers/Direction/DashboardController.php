<?php
// app/Http/Controllers/Direction/DashboardController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Decision;
use App\Models\Simulation;
use App\Models\Temps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Statistiques globales
        $stats = $this->getGlobalStats();
        
        // Performances par service
        $servicePerformances = $this->getServicePerformances();
        
        // Évolution du CA
        $revenueEvolution = $this->getRevenueEvolution();
        
        // Alertes et indicateurs critiques
        $alerts = $this->getAlerts();
        
        // Dernières décisions
        $recentDecisions = Decision::with('createur')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Dernières simulations
        $recentSimulations = Simulation::with('utilisateur')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Top services par CA
        $topServices = $this->getTopServices();
        
        // Répartition des dépenses par service
        $expenseBreakdown = $this->getExpenseBreakdown();
        
        // Tendance mensuelle
        $monthlyTrend = $this->getMonthlyTrend();
        
        return view('direction.dashboard', compact(
            'stats',
            'servicePerformances',
            'revenueEvolution',
            'alerts',
            'recentDecisions',
            'recentSimulations',
            'topServices',
            'expenseBreakdown',
            'monthlyTrend'
        ));
    }
    
    private function getGlobalStats()
    {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        // CA total
        $totalCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum('montant_usd');
        
        // CA année précédente
        $lastYearCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($lastYear) {
            $q->where('annee', $lastYear);
        })->sum('montant_usd');
        
        // Marge totale
        $totalMarge = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'MARGE');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum('montant_usd');
        
        // Trésorerie
        $tresorerie = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'TRESO');
        })->sum('montant_usd');
        
        // Investissements
        $investissements = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'INVEST');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum('montant_usd');
        
        // Services actifs
        $servicesActifs = Service::where('est_actif', true)->count();
        
        // Utilisateurs actifs
        $utilisateursActifs = Utilisateur::where('est_actif', true)->count();
        
        // Taux de croissance CA
        $croissanceCA = $lastYearCA > 0 ? (($totalCA - $lastYearCA) / $lastYearCA) * 100 : 0;
        
        // Marge brute en pourcentage
        $margePercent = $totalCA > 0 ? ($totalMarge / $totalCA) * 100 : 0;
        
        return [
            'total_ca' => $totalCA,
            'last_year_ca' => $lastYearCA,
            'croissance_ca' => $croissanceCA,
            'total_marge' => $totalMarge,
            'marge_percent' => $margePercent,
            'tresorerie' => $tresorerie,
            'investissements' => $investissements,
            'services_actifs' => $servicesActifs,
            'utilisateurs_actifs' => $utilisateursActifs,
        ];
    }
    
    private function getServicePerformances()
    {
        $currentYear = date('Y');
        
        return Service::where('est_actif', true)
            ->withCount('utilisateurs')
            ->withSum(['financeFaits as ca' => function($q) use ($currentYear) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'CA');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], 'montant_usd')
            ->withSum(['financeFaits as marge' => function($q) use ($currentYear) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'MARGE');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], 'montant_usd')
            ->withSum(['financeFaits as depenses' => function($q) use ($currentYear) {
                $q->whereHas('activite', function($sq) {
                    $sq->whereIn('type_activite', ['depense', 'achat']);
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], 'montant_usd')
            ->orderBy('ca', 'desc')
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id_service,
                    'name' => $service->nom_service,
                    'ca' => $service->ca ?? 0,
                    'marge' => $service->marge ?? 0,
                    'depenses' => $service->depenses ?? 0,
                    'employes' => $service->utilisateurs_count ?? 0,
                    'performance' => $service->ca > 0 ? round(($service->marge / $service->ca) * 100, 1) : 0,
                    'couleur' => $service->couleur ?? '#1a56db'
                ];
            });
    }
    
    private function getRevenueEvolution()
    {
        $months = range(1, 12);
        $data = [];
        
        foreach ($months as $month) {
            $ca = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($month) {
                $q->where('mois', $month)->where('annee', date('Y'));
            })->sum('montant_usd');
            
            $caPrecedent = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($month) {
                $q->where('mois', $month)->where('annee', date('Y') - 1);
            })->sum('montant_usd');
            
            $data[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'current' => $ca,
                'previous' => $caPrecedent,
                'growth' => $caPrecedent > 0 ? round(($ca - $caPrecedent) / $caPrecedent * 100, 1) : 0
            ];
        }
        
        return $data;
    }
    
    private function getAlerts()
    {
        // Indicateurs en alerte
        $indicateursAlerte = Indicateur::where('est_actif', true)
            ->where(function($q) {
                $q->whereRaw('seuil_alerte_min > (SELECT SUM(montant_usd) FROM finance_faits WHERE id_indicateur = indicateurs.id_indicateur)')
                  ->orWhereRaw('seuil_alerte_max < (SELECT SUM(montant_usd) FROM finance_faits WHERE id_indicateur = indicateurs.id_indicateur)');
            })
            ->get();
        
        // Services avec CA en baisse
        $servicesBaisse = Service::whereHas('financeFaits', function($q) {
            $q->whereHas('indicateur', function($sq) {
                $sq->where('code_kpi', 'CA');
            })->whereHas('temps', function($tq) {
                $tq->where('mois', date('m'));
            });
        })->get()->filter(function($service) {
            $caMois = $service->financeFaits()
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) { $q->where('mois', date('m')); })
                ->sum('montant_usd');
                
            $caMoisPrecedent = $service->financeFaits()
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) { $q->where('mois', date('m', strtotime('-1 month'))); })
                ->sum('montant_usd');
                
            return $caMoisPrecedent > 0 && (($caMois - $caMoisPrecedent) / $caMoisPrecedent) < -0.1;
        });
        
        return [
            'indicateurs' => $indicateursAlerte,
            'services_baisse' => $servicesBaisse,
            'count' => $indicateursAlerte->count() + $servicesBaisse->count()
        ];
    }
    
    private function getTopServices()
    {
        $currentYear = date('Y');
        
        return Service::where('est_actif', true)
            ->withSum(['financeFaits as total' => function($q) use ($currentYear) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'CA');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], 'montant_usd')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($service) {
                return [
                    'name' => $service->nom_service,
                    'total' => $service->total ?? 0,
                    'couleur' => $service->couleur ?? '#1a56db'
                ];
            });
    }
    
    private function getExpenseBreakdown()
    {
        $currentYear = date('Y');
        
        $categories = ['vente', 'achat', 'depense', 'investissement'];
        $data = [];
        
        foreach ($categories as $cat) {
            $total = FinanceFait::whereHas('activite', function($q) use ($cat) {
                $q->where('type_activite', $cat);
            })->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })->sum('montant_usd');
            
            if ($total > 0) {
                $data[] = [
                    'category' => ucfirst($cat),
                    'total' => $total,
                    'icon' => $this->getIconForCategory($cat)
                ];
            }
        }
        
        return $data;
    }
    
    private function getMonthlyTrend()
    {
        $months = range(1, 12);
        $trend = [];
        
        foreach ($months as $month) {
            $ca = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($month) {
                $q->where('mois', $month)->where('annee', date('Y'));
            })->sum('montant_usd');
            
            $trend[] = $ca;
        }
        
        return $trend;
    }
    
    private function getIconForCategory($category)
    {
        $icons = [
            'vente' => 'fa-shopping-cart',
            'achat' => 'fa-truck',
            'depense' => 'fa-receipt',
            'investissement' => 'fa-chart-line'
        ];
        return $icons[$category] ?? 'fa-circle';
    }
}