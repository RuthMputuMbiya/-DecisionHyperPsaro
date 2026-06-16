<?php
// app/Http/Controllers/Finance/DashboardController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
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
    /**
     * Afficher le tableau de bord financier
     */
    public function index(Request $request)
    {
        // Récupérer la devise sélectionnée
        $devise = session('devise', 'USD');
        
        // Statistiques globales
        $stats = $this->getGlobalStats($devise);
        
        // Évolution du CA
        $revenueEvolution = $this->getRevenueEvolution($devise);
        
        // Performance par service
        $servicePerformance = $this->getServicePerformance($devise);
        
        // Indicateurs avec alertes
        $alertIndicators = $this->getAlertIndicators($devise);
        
        // Tendances
        $trends = $this->getTrends($devise);
        
        // Prévisions
        $forecasts = $this->getForecasts($devise);
        
        // Dernières décisions
        $recentDecisions = Decision::with('createur')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Top services
        $topServices = $this->getTopServices($devise);
        
        return view('finance.dashboard', compact(
            'stats',
            'revenueEvolution',
            'servicePerformance',
            'alertIndicators',
            'trends',
            'forecasts',
            'recentDecisions',
            'topServices',
            'devise'
        ));
    }
    
    /**
     * Analyse financière détaillée
     */
    public function analyse(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $serviceId = $request->get('service_id');
        
        // Données pour l'analyse
        $monthlyData = $this->getMonthlyAnalysis($devise, $year, $serviceId);
        
        // Comparaison avec année précédente
        $yearComparison = $this->getYearComparison($devise, $year, $serviceId);
        
        // Ratios financiers
        $ratios = $this->getFinancialRatios($devise, $year, $serviceId);
        
        // Services disponibles
        $services = Service::where('est_actif', true)->get();
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.analyses.index', compact(
            'monthlyData',
            'yearComparison',
            'ratios',
            'services',
            'years',
            'devise',
            'year',
            'serviceId'
        ));
    }
    
    /**
     * Obtenir les statistiques globales
     */
    private function getGlobalStats($devise)
    {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        
        // CA total
        $totalCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}");
        
        // CA année précédente
        $lastYearCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($lastYear) {
            $q->where('annee', $lastYear);
        })->sum("montant_{$devise}");
        
        // Marge
        $totalMarge = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'MARGE');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}");
        
        // Trésorerie
        $tresorerie = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'TRESO');
        })->sum("montant_{$devise}");
        
        // Dépenses
        $depenses = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}");
        
        // Taux de croissance
        $croissanceCA = $lastYearCA > 0 ? round(($totalCA - $lastYearCA) / $lastYearCA * 100, 1) : 0;
        
        // Taux de marge
        $tauxMarge = $totalCA > 0 ? round($totalMarge / $totalCA * 100, 1) : 0;
        
        // Taux d'endettement (simulation)
        $tauxEndettement = $totalCA > 0 ? round(($depenses / $totalCA) * 100, 1) : 0;
        
        return [
            'ca_total' => $totalCA,
            'ca_precedent' => $lastYearCA,
            'croissance_ca' => $croissanceCA,
            'marge_totale' => $totalMarge,
            'taux_marge' => $tauxMarge,
            'tresorerie' => $tresorerie,
            'depenses' => $depenses,
            'taux_endettement' => $tauxEndettement
        ];
    }
    
    /**
     * Obtenir l'évolution du CA
     */
    private function getRevenueEvolution($devise)
    {
        $months = range(1, 12);
        $data = [];
        
        foreach ($months as $month) {
            $ca = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($month) {
                $q->where('mois', $month)->where('annee', date('Y'));
            })->sum("montant_{$devise}");
            
            $caPrecedent = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($month) {
                $q->where('mois', $month)->where('annee', date('Y') - 1);
            })->sum("montant_{$devise}");
            
            $data[] = [
                'month' => Carbon::createFromDate(null, $month, 1)->translatedFormat('M'),
                'current' => $ca,
                'previous' => $caPrecedent,
                'growth' => $caPrecedent > 0 ? round(($ca - $caPrecedent) / $caPrecedent * 100, 1) : 0
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtenir la performance par service
     */
    private function getServicePerformance($devise)
    {
        $currentYear = date('Y');
        
        return Service::where('est_actif', true)
            ->withSum(['financeFaits as ca' => function($q) use ($currentYear, $devise) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'CA');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], "montant_{$devise}")
            ->withSum(['financeFaits as marge' => function($q) use ($currentYear, $devise) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'MARGE');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], "montant_{$devise}")
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id_service,
                    'name' => $service->nom_service,
                    'ca' => $service->ca ?? 0,
                    'marge' => $service->marge ?? 0,
                    'performance' => $service->ca > 0 ? round(($service->marge / $service->ca) * 100, 1) : 0,
                    'couleur' => $service->couleur ?? '#1a56db'
                ];
            });
    }
    
    /**
     * Obtenir les indicateurs en alerte
     */
    private function getAlertIndicators($devise)
    {
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $alertes = [];
        
        foreach ($indicateurs as $indicateur) {
            $valeur = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) {
                    $q->where('annee', date('Y'));
                })
                ->sum("montant_{$devise}");
            
            $estAlerte = false;
            $seuil = null;
            
            if ($indicateur->seuil_alerte_min && $valeur < $indicateur->seuil_alerte_min) {
                $estAlerte = true;
                $seuil = $indicateur->seuil_alerte_min;
            } elseif ($indicateur->seuil_alerte_max && $valeur > $indicateur->seuil_alerte_max) {
                $estAlerte = true;
                $seuil = $indicateur->seuil_alerte_max;
            }
            
            if ($estAlerte) {
                $alertes[] = [
                    'nom' => $indicateur->nom_kpi,
                    'code' => $indicateur->code_kpi,
                    'valeur' => $valeur,
                    'seuil' => $seuil,
                    'type' => $indicateur->seuil_alerte_min && $valeur < $indicateur->seuil_alerte_min ? 'min' : 'max',
                    'icone' => $indicateur->icone
                ];
            }
        }
        
        return $alertes;
    }
    
    /**
     * Obtenir les tendances
     */
    private function getTrends($devise)
    {
        $currentYear = date('Y');
        
        // Tendance CA
        $caLast3Months = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear)->where('mois', '>=', date('m') - 2);
        })->sum("montant_{$devise}");
        
        $caPrevious3Months = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear)->where('mois', '<', date('m') - 2)->where('mois', '>=', date('m') - 5);
        })->sum("montant_{$devise}");
        
        $caTrend = $caPrevious3Months > 0 ? round(($caLast3Months - $caPrevious3Months) / $caPrevious3Months * 100, 1) : 0;
        
        // Tendance dépenses
        $depensesLast3 = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear)->where('mois', '>=', date('m') - 2);
        })->sum("montant_{$devise}");
        
        $depensesPrevious3 = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear)->where('mois', '<', date('m') - 2)->where('mois', '>=', date('m') - 5);
        })->sum("montant_{$devise}");
        
        $depensesTrend = $depensesPrevious3 > 0 ? round(($depensesLast3 - $depensesPrevious3) / $depensesPrevious3 * 100, 1) : 0;
        
        return [
            'ca' => ['value' => $caTrend, 'direction' => $caTrend >= 0 ? 'up' : 'down'],
            'depenses' => ['value' => $depensesTrend, 'direction' => $depensesTrend >= 0 ? 'up' : 'down']
        ];
    }
    
    /**
     * Obtenir les prévisions
     */
    private function getForecasts($devise)
    {
        // Prévision simple basée sur la moyenne des 3 derniers mois
        $currentYear = date('Y');
        $last3MonthsAvg = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear)->where('mois', '>=', date('m') - 2);
        })->avg("montant_{$devise}");
        
        $forecastNextMonth = round($last3MonthsAvg, 2);
        $forecastNextQuarter = round($last3MonthsAvg * 3, 2);
        $forecastNextYear = round($last3MonthsAvg * 12, 2);
        
        return [
            'next_month' => $forecastNextMonth,
            'next_quarter' => $forecastNextQuarter,
            'next_year' => $forecastNextYear
        ];
    }
    
    /**
     * Obtenir les top services
     */
    private function getTopServices($devise)
    {
        $currentYear = date('Y');
        
        return Service::where('est_actif', true)
            ->withSum(['financeFaits as total' => function($q) use ($currentYear, $devise) {
                $q->whereHas('indicateur', function($sq) {
                    $sq->where('code_kpi', 'CA');
                })->whereHas('temps', function($tq) use ($currentYear) {
                    $tq->where('annee', $currentYear);
                });
            }], "montant_{$devise}")
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get()
            ->map(function($service) {
                return [
                    'name' => $service->nom_service,
                    'ca' => $service->total ?? 0,
                    'couleur' => $service->couleur ?? '#1a56db'
                ];
            });
    }
    
    /**
     * Analyse mensuelle
     */
    private function getMonthlyAnalysis($devise, $year, $serviceId = null)
    {
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $query = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($i, $year) {
                $q->where('mois', $i)->where('annee', $year);
            });
            
            if ($serviceId) {
                $query->where('id_service', $serviceId);
            }
            
            $ca = $query->sum("montant_{$devise}");
            
            $queryMarge = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'MARGE');
            })->whereHas('temps', function($q) use ($i, $year) {
                $q->where('mois', $i)->where('annee', $year);
            });
            
            if ($serviceId) {
                $queryMarge->where('id_service', $serviceId);
            }
            
            $marge = $queryMarge->sum("montant_{$devise}");
            
            $data[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->translatedFormat('F'),
                'ca' => $ca,
                'marge' => $marge,
                'taux_marge' => $ca > 0 ? round($marge / $ca * 100, 1) : 0
            ];
        }
        
        return $data;
    }
    
    /**
     * Comparaison année
     */
    private function getYearComparison($devise, $year, $serviceId = null)
    {
        $currentYearTotal = $this->getYearTotal($year, $devise, $serviceId);
        $prevYearTotal = $this->getYearTotal($year - 1, $devise, $serviceId);
        
        $variation = $prevYearTotal > 0 ? round(($currentYearTotal - $prevYearTotal) / $prevYearTotal * 100, 1) : 0;
        
        return [
            'current_year' => $currentYearTotal,
            'previous_year' => $prevYearTotal,
            'variation' => $variation,
            'evolution' => $variation >= 0 ? 'up' : 'down'
        ];
    }
    
    /**
     * Ratios financiers
     */
    private function getFinancialRatios($devise, $year, $serviceId = null)
    {
        $ca = $this->getYearTotal($year, $devise, $serviceId);
        $marge = $this->getYearTotal($year, $devise, $serviceId, 'MARGE');
        
        $depenses = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        });
        
        if ($serviceId) {
            $depenses->where('id_service', $serviceId);
        }
        $totalDepenses = $depenses->sum("montant_{$devise}");
        
        return [
            'marge_nette' => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
            'ratio_depenses' => $ca > 0 ? round($totalDepenses / $ca * 100, 1) : 0,
            'rentabilite' => $ca > 0 ? round(($marge - $totalDepenses) / $ca * 100, 1) : 0
        ];
    }
    
    private function getYearTotal($year, $devise, $serviceId = null, $kpi = 'CA')
    {
        $query = FinanceFait::whereHas('indicateur', function($q) use ($kpi) {
            $q->where('code_kpi', $kpi);
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        });
        
        if ($serviceId) {
            $query->where('id_service', $serviceId);
        }
        
        return $query->sum("montant_{$devise}");
    }
}