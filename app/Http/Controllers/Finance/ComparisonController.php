<?php
// app/Http/Controllers/Finance/ComparisonController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComparisonController extends Controller
{
    /**
     * Page principale des comparaisons
     */
    public function index(Request $request)
    {
        $devise = session('devise', 'USD');
        
        $yearlyComparison = $this->compareYears($devise);
        $servicesComparison = $this->compareServices($devise);
        
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $services = Service::where('est_actif', true)->get();
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.comparaisons.index', compact(
            'yearlyComparison', 'servicesComparison', 'indicateurs', 'services', 'years', 'devise'
        ));
    }
    
    /**
     * 🔥 COMPARAISON PAR PÉRIODES - CORRIGÉE
     */
    public function periodes(Request $request)
    {
        $devise = session('devise', 'USD');
        
        // Récupérer les paramètres
        $period1 = $request->get('period1', date('Y-m'));
        $period2 = $request->get('period2', date('Y-m', strtotime('-1 month')));
        $indicateurId = $request->get('indicateur_id');
        
        // 🔥 CORRECTION : Si les périodes sont identiques, prendre le mois précédent pour period2
        if ($period1 == $period2) {
            $date = Carbon::createFromFormat('Y-m', $period1);
            $period2 = $date->subMonth()->format('Y-m');
        }
        
        // Récupérer les indicateurs
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        // Données pour la période 1
        $data1 = $this->getPeriodData($period1, $devise, $indicateurId);
        
        // Données pour la période 2
        $data2 = $this->getPeriodData($period2, $devise, $indicateurId);
        
        // Calcul des écarts
        $comparison = $this->calculateComparison($data1, $data2);
        
        // Détails par service
        $servicesData = $this->getServicesPeriodData($period1, $period2, $devise, $indicateurId);
        
        // Périodes disponibles
        $availablePeriods = $this->getAvailablePeriods();
        
        return view('finance.comparaisons.periodes', compact(
            'data1', 'data2', 'comparison', 'servicesData',
            'indicateurs', 'availablePeriods', 'period1', 'period2', 'indicateurId', 'devise'
        ));
    }
    
    /**
     * Comparaison par services
     */
    public function services(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $indicateurId = $request->get('indicateur_id');
        
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $servicesData = $this->getServicesComparisonData($year, $devise, $indicateurId);
        
        $topServices = collect($servicesData)->sortByDesc('valeur')->take(5);
        $bottomServices = collect($servicesData)->sortBy('valeur')->take(5);
        
        $moyenne = collect($servicesData)->avg('valeur');
        $mediane = $this->calculateMedian(collect($servicesData)->pluck('valeur')->toArray());
        $distribution = $this->getDistribution($servicesData);
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.comparaisons.services', compact(
            'servicesData', 'topServices', 'bottomServices', 'indicateurs',
            'moyenne', 'mediane', 'distribution', 'year', 'indicateurId', 'years', 'devise'
        ));
    }
    
    /**
     * Comparaison par indicateurs
     */
    public function indicateurs(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $serviceId = $request->get('service_id');
        
        $services = Service::where('est_actif', true)->get();
        $indicateursData = $this->getIndicateursComparisonData($year, $devise, $serviceId);
        $evolution = $this->getIndicateursEvolution($year, $devise, $serviceId);
        $correlation = $this->getIndicateursCorrelation($year, $devise, $serviceId);
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.comparaisons.indicateurs', compact(
            'indicateursData', 'evolution', 'correlation', 'services',
            'year', 'serviceId', 'years', 'devise'
        ));
    }
    
    /**
     * API: Obtenir les données pour un graphique (AJAX)
     */
    public function chartData(Request $request)
    {
        $type = $request->get('type');
        $devise = session('devise', 'USD');
        
        switch ($type) {
            case 'periods':
                $period1 = $request->get('period1');
                $period2 = $request->get('period2');
                $indicateurId = $request->get('indicateur_id');
                $data = $this->getPeriodComparisonChartData($period1, $period2, $devise, $indicateurId);
                break;
            case 'services':
                $year = $request->get('year');
                $indicateurId = $request->get('indicateur_id');
                $data = $this->getServicesChartData($year, $devise, $indicateurId);
                break;
            default:
                $data = [];
        }
        
        return response()->json($data);
    }
    
    // ========== MÉTHODES PRIVÉES ==========
    
    private function compareYears($devise)
    {
        $currentYear = date('Y');
        $data = [];
        
        for ($i = 2; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $ca = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })->sum("montant_{$devise}");
            
            $marge = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'MARGE');
            })->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })->sum("montant_{$devise}");
            
            $data[] = [
                'year' => $year,
                'ca' => $ca,
                'marge' => $marge,
                'croissance' => $i < 2 && isset($data[count($data)-1]) ? 
                    ($data[count($data)-1]['ca'] > 0 ? round(($ca - $data[count($data)-1]['ca']) / $data[count($data)-1]['ca'] * 100, 1) : 0) : 0
            ];
        }
        
        return $data;
    }
    
    private function compareServices($devise)
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
            ->orderBy('ca', 'desc')
            ->get()
            ->map(function($service) {
                return [
                    'name' => $service->nom_service,
                    'value' => $service->ca ?? 0,
                    'couleur' => $service->couleur ?? '#1a56db'
                ];
            });
    }
    
    /**
     * 🔥 OBTENIR LES DONNÉES D'UNE PÉRIODE - CORRIGÉ
     */
    private function getPeriodData($period, $devise, $indicateurId = null)
    {
        // Vérifier le format
        if (strpos($period, '-') === false) {
            return ['period' => $period, 'total' => 0, 'details' => []];
        }
        
        list($year, $month) = explode('-', $period);
        $isYear = ($month == 'all');
        
        $query = FinanceFait::whereHas('temps', function($q) use ($year, $month, $isYear) {
            $q->where('annee', $year);
            if (!$isYear) {
                $q->where('mois', $month);
            }
        });
        
        if ($indicateurId) {
            $query->where('id_indicateur', $indicateurId);
        }
        
        $total = $query->sum("montant_{$devise}");
        
        // Détails par service
        $services = Service::where('est_actif', true)->get();
        $details = [];
        
        foreach ($services as $service) {
            $serviceQuery = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('temps', function($q) use ($year, $month, $isYear) {
                    $q->where('annee', $year);
                    if (!$isYear) {
                        $q->where('mois', $month);
                    }
                });
            
            if ($indicateurId) {
                $serviceQuery->where('id_indicateur', $indicateurId);
            }
            
            $value = $serviceQuery->sum("montant_{$devise}");
            
            if ($value > 0) {
                $details[] = [
                    'service' => $service->nom_service,
                    'value' => $value,
                    'percentage' => $total > 0 ? round($value / $total * 100, 1) : 0
                ];
            }
        }
        
        return [
            'period' => $isYear ? "Année $year" : Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'year' => $year,
            'month' => $month,
            'total' => $total,
            'details' => $details
        ];
    }
    
    /**
     * 🔥 CALCULER LES ÉCARTS - CORRIGÉ
     */
    private function calculateComparison($data1, $data2)
    {
        $total1 = $data1['total'];
        $total2 = $data2['total'];
        
        $difference = $total2 - $total1;
        $percentage = $total1 > 0 ? round($difference / $total1 * 100, 1) : ($total2 > 0 ? 100 : 0);
        
        // Évolution par service
        $servicesEvolution = [];
        $allServices = array_merge(
            array_column($data1['details'], 'service'),
            array_column($data2['details'], 'service')
        );
        $allServices = array_unique($allServices);
        
        foreach ($allServices as $service) {
            $value1 = 0;
            $value2 = 0;
            
            foreach ($data1['details'] as $detail) {
                if ($detail['service'] == $service) $value1 = $detail['value'];
            }
            foreach ($data2['details'] as $detail) {
                if ($detail['service'] == $service) $value2 = $detail['value'];
            }
            
            $diff = $value2 - $value1;
            $perc = $value1 > 0 ? round($diff / $value1 * 100, 1) : ($value2 > 0 ? 100 : 0);
            
            $servicesEvolution[] = [
                'service' => $service,
                'valeur1' => $value1,
                'valeur2' => $value2,
                'difference' => $diff,
                'pourcentage' => $perc,
                'direction' => $diff >= 0 ? 'up' : 'down'
            ];
        }
        
        return [
            'difference' => $difference,
            'percentage' => $percentage,
            'direction' => $difference >= 0 ? 'up' : 'down',
            'services' => $servicesEvolution
        ];
    }
    
    /**
     * Obtenir les données des services par période
     */
    private function getServicesPeriodData($period1, $period2, $devise, $indicateurId = null)
    {
        list($year1, $month1) = explode('-', $period1);
        list($year2, $month2) = explode('-', $period2);
        
        $services = Service::where('est_actif', true)->get();
        $data = [];
        
        foreach ($services as $service) {
            $value1 = $this->getServicePeriodValue($service->id_service, $year1, $month1, $devise, $indicateurId);
            $value2 = $this->getServicePeriodValue($service->id_service, $year2, $month2, $devise, $indicateurId);
            
            if ($value1 > 0 || $value2 > 0) {
                $data[] = [
                    'service' => $service->nom_service,
                    'couleur' => $service->couleur,
                    'valeur1' => $value1,
                    'valeur2' => $value2,
                    'difference' => $value2 - $value1,
                    'variation' => $value1 > 0 ? round(($value2 - $value1) / $value1 * 100, 1) : ($value2 > 0 ? 100 : 0)
                ];
            }
        }
        
        return $data;
    }
    
    private function getServicePeriodValue($serviceId, $year, $month, $devise, $indicateurId = null)
    {
        $isYear = ($month == 'all');
        
        $query = FinanceFait::where('id_service', $serviceId)
            ->whereHas('temps', function($q) use ($year, $month, $isYear) {
                $q->where('annee', $year);
                if (!$isYear) {
                    $q->where('mois', $month);
                }
            });
        
        if ($indicateurId) {
            $query->where('id_indicateur', $indicateurId);
        }
        
        return $query->sum("montant_{$devise}");
    }
    
    private function getServicesComparisonData($year, $devise, $indicateurId = null)
    {
        $services = Service::where('est_actif', true)->get();
        $data = [];
        
        foreach ($services as $service) {
            $query = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('temps', function($q) use ($year) {
                    $q->where('annee', $year);
                });
            
            if ($indicateurId) {
                $query->where('id_indicateur', $indicateurId);
            } else {
                $query->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                });
            }
            
            $value = $query->sum("montant_{$devise}");
            
            $data[] = [
                'service_id' => $service->id_service,
                'service' => $service->nom_service,
                'valeur' => $value,
                'couleur' => $service->couleur,
                'employes' => $service->utilisateurs()->count(),
                'performance' => $this->getServicePerformance($service->id_service, $year, $devise)
            ];
        }
        
        return collect($data)->sortByDesc('valeur')->values()->toArray();
    }
    
    private function getServicePerformance($serviceId, $year, $devise)
    {
        $ca = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($year) { $q->where('annee', $year); })
            ->sum("montant_{$devise}");
        
        $marge = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
            ->whereHas('temps', function($q) use ($year) { $q->where('annee', $year); })
            ->sum("montant_{$devise}");
        
        return $ca > 0 ? round($marge / $ca * 100, 1) : 0;
    }
    
    private function calculateMedian($array)
    {
        sort($array);
        $count = count($array);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return round(($array[$middle - 1] + $array[$middle]) / 2, 2);
        } else {
            return round($array[$middle], 2);
        }
    }
    
    private function getDistribution($data)
    {
        $values = collect($data)->pluck('valeur')->filter()->toArray();
        if (empty($values)) return ['tranches' => [], 'counts' => []];
        
        $max = max($values);
        $min = min($values);
        $step = ($max - $min) / 5;
        
        $tranches = [];
        $counts = [];
        
        for ($i = 0; $i < 5; $i++) {
            $lower = $min + $i * $step;
            $upper = $min + ($i + 1) * $step;
            $tranches[] = round($lower) . ' - ' . round($upper);
            $counts[] = count(array_filter($values, function($v) use ($lower, $upper) {
                return $v >= $lower && $v <= $upper;
            }));
        }
        
        return ['tranches' => $tranches, 'counts' => $counts];
    }
    
    private function getIndicateursComparisonData($year, $devise, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $data = [];
        
        foreach ($indicateurs as $indicateur) {
            $query = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) use ($year) {
                    $q->where('annee', $year);
                });
            
            if ($serviceId) {
                $query->where('id_service', $serviceId);
            }
            
            $value = $query->sum("montant_{$devise}");
            
            $data[] = [
                'id' => $indicateur->id_indicateur,
                'nom' => $indicateur->nom_kpi,
                'code' => $indicateur->code_kpi,
                'valeur' => $value,
                'unite' => $indicateur->unite,
                'icone' => $indicateur->icone,
                'couleur' => $indicateur->couleur_alerte,
                'seuil_min' => $indicateur->seuil_alerte_min,
                'seuil_max' => $indicateur->seuil_alerte_max
            ];
        }
        
        return collect($data)->sortByDesc('valeur')->values()->toArray();
    }
    
    private function getIndicateursEvolution($year, $devise, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->limit(5)->get();
        $evolution = [];
        
        foreach ($indicateurs as $indicateur) {
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $query = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                    ->whereHas('temps', function($q) use ($i, $year) {
                        $q->where('mois', $i)->where('annee', $year);
                    });
                
                if ($serviceId) {
                    $query->where('id_service', $serviceId);
                }
                
                $monthlyData[] = $query->sum("montant_{$devise}");
            }
            
            $evolution[] = [
                'nom' => $indicateur->nom_kpi,
                'code' => $indicateur->code_kpi,
                'data' => $monthlyData,
                'couleur' => $indicateur->couleur_alerte
            ];
        }
        
        return $evolution;
    }
    
    private function getIndicateursCorrelation($year, $devise, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->limit(4)->get();
        $correlations = [];
        
        $indicateursList = $indicateurs->values();
        
        for ($i = 0; $i < count($indicateursList); $i++) {
            for ($j = $i + 1; $j < count($indicateursList); $j++) {
                $data1 = [];
                $data2 = [];
                
                for ($month = 1; $month <= 12; $month++) {
                    $query1 = FinanceFait::where('id_indicateur', $indicateursList[$i]->id_indicateur)
                        ->whereHas('temps', function($q) use ($month, $year) {
                            $q->where('mois', $month)->where('annee', $year);
                        });
                    
                    $query2 = FinanceFait::where('id_indicateur', $indicateursList[$j]->id_indicateur)
                        ->whereHas('temps', function($q) use ($month, $year) {
                            $q->where('mois', $month)->where('annee', $year);
                        });
                    
                    if ($serviceId) {
                        $query1->where('id_service', $serviceId);
                        $query2->where('id_service', $serviceId);
                    }
                    
                    $data1[] = $query1->sum("montant_{$devise}");
                    $data2[] = $query2->sum("montant_{$devise}");
                }
                
                $correlation = $this->calculateCorrelation($data1, $data2);
                
                $correlations[] = [
                    'indicateur1' => $indicateursList[$i]->nom_kpi,
                    'indicateur2' => $indicateursList[$j]->nom_kpi,
                    'correlation' => round($correlation, 2),
                    'interpretation' => $this->getCorrelationInterpretation($correlation)
                ];
            }
        }
        
        return $correlations;
    }
    
    private function calculateCorrelation($x, $y)
    {
        $n = count($x);
        if ($n == 0) return 0;
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $y));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        $sumY2 = array_sum(array_map(function($yi) { return $yi * $yi; }, $y));
        
        $denominator = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));
        
        return $denominator != 0 ? ($n * $sumXY - $sumX * $sumY) / $denominator : 0;
    }
    
    private function getCorrelationInterpretation($correlation)
    {
        $abs = abs($correlation);
        if ($abs >= 0.8) return 'Très forte';
        if ($abs >= 0.6) return 'Forte';
        if ($abs >= 0.4) return 'Modérée';
        if ($abs >= 0.2) return 'Faible';
        return 'Très faible';
    }
    
    /**
     * Obtenir les périodes disponibles
     */
    private function getAvailablePeriods()
    {
        $periods = [];
        
        // Derniers mois
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $periods[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y')
            ];
        }
        
        // Années
        for ($i = 0; $i < 3; $i++) {
            $year = date('Y') - $i;
            $periods[] = [
                'value' => $year . '-all',
                'label' => "Année $year"
            ];
        }
        
        return $periods;
    }
    
    private function getPeriodComparisonChartData($period1, $period2, $devise, $indicateurId = null)
    {
        $data1 = $this->getPeriodData($period1, $devise, $indicateurId);
        $data2 = $this->getPeriodData($period2, $devise, $indicateurId);
        
        $services = array_unique(array_merge(
            array_column($data1['details'], 'service'),
            array_column($data2['details'], 'service')
        ));
        
        $values1 = [];
        $values2 = [];
        
        foreach ($services as $service) {
            $val1 = 0;
            $val2 = 0;
            
            foreach ($data1['details'] as $d) {
                if ($d['service'] == $service) $val1 = $d['value'];
            }
            foreach ($data2['details'] as $d) {
                if ($d['service'] == $service) $val2 = $d['value'];
            }
            
            $values1[] = $val1;
            $values2[] = $val2;
        }
        
        return [
            'services' => $services,
            'values1' => $values1,
            'values2' => $values2,
            'total1' => $data1['total'],
            'total2' => $data2['total']
        ];
    }
    
    private function getServicesChartData($year, $devise, $indicateurId = null)
    {
        $data = $this->getServicesComparisonData($year, $devise, $indicateurId);
        
        return [
            'services' => array_column($data, 'service'),
            'values' => array_column($data, 'valeur')
        ];
    }
}