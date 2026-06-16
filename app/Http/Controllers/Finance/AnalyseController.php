<?php
// app/Http/Controllers/Finance/AnalyseController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Temps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyseController extends Controller
{
    /**
     * Page principale des analyses
     */
    public function index(Request $request)
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
     * Page des tendances
     */
    public function tendances(Request $request)
    {
        $devise = session('devise', 'USD');
        $period = $request->get('period', 'year'); // month, quarter, year
        
        // Données de tendance
        $trendData = $this->getTrendData($devise, $period);
        
        // Indicateurs de tendance
        $indicators = $this->getTrendIndicators($devise);
        
        // Projections
        $projections = $this->getProjections($devise);
        
        // Saisonnalité
        $seasonality = $this->getSeasonality($devise);
        
        return view('finance.analyses.tendances', compact(
            'trendData',
            'indicators',
            'projections',
            'seasonality',
            'devise',
            'period'
        ));
    }
    
    /**
     * Page des prévisions
     */
    public function previsions(Request $request)
    {
        $devise = session('devise', 'USD');
        $months = $request->get('months', 6);
        
        // Prévisions CA
        $caForecast = $this->getCAForecast($devise, $months);
        
        // Prévisions Marge
        $margeForecast = $this->getMargeForecast($devise, $months);
        
        // Scénarios
        $scenarios = $this->getScenarios($devise);
        
        // Analyse de sensibilité
        $sensitivity = $this->getSensitivityAnalysis($devise);
        
        return view('finance.analyses.previsions', compact(
            'caForecast',
            'margeForecast',
            'scenarios',
            'sensitivity',
            'devise',
            'months'
        ));
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
            
            // Mois précédent pour variation
            $prevMonth = $i == 1 ? 12 : $i - 1;
            $prevYear = $i == 1 ? $year - 1 : $year;
            
            $prevCa = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($prevMonth, $prevYear) {
                $q->where('mois', $prevMonth)->where('annee', $prevYear);
            });
            
            if ($serviceId) {
                $prevCa->where('id_service', $serviceId);
            }
            
            $prevCaValue = $prevCa->sum("montant_{$devise}");
            
            $variation = $prevCaValue > 0 ? round(($ca - $prevCaValue) / $prevCaValue * 100, 1) : 0;
            
            $data[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->translatedFormat('F'),
                'month_num' => $i,
                'ca' => $ca,
                'marge' => $marge,
                'taux_marge' => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
                'variation' => $variation,
                'variation_direction' => $variation >= 0 ? 'up' : 'down'
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
        $twoYearsAgoTotal = $this->getYearTotal($year - 2, $devise, $serviceId);
        
        $variation = $prevYearTotal > 0 ? round(($currentYearTotal - $prevYearTotal) / $prevYearTotal * 100, 1) : 0;
        $variation2 = $twoYearsAgoTotal > 0 ? round(($prevYearTotal - $twoYearsAgoTotal) / $twoYearsAgoTotal * 100, 1) : 0;
        
        // Évolution mensuelle comparée
        $monthlyComparison = [];
        for ($i = 1; $i <= 12; $i++) {
            $current = $this->getMonthTotal($year, $i, $devise, $serviceId);
            $prev = $this->getMonthTotal($year - 1, $i, $devise, $serviceId);
            
            $monthlyComparison[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->translatedFormat('M'),
                'current' => $current,
                'previous' => $prev,
                'variation' => $prev > 0 ? round(($current - $prev) / $prev * 100, 1) : 0
            ];
        }
        
        return [
            'current_year' => $currentYearTotal,
            'previous_year' => $prevYearTotal,
            'two_years_ago' => $twoYearsAgoTotal,
            'variation' => $variation,
            'variation_prev' => $variation2,
            'evolution' => $variation >= 0 ? 'up' : 'down',
            'monthly_comparison' => $monthlyComparison
        ];
    }
    
    /**
     * Ratios financiers
     */
    private function getFinancialRatios($devise, $year, $serviceId = null)
    {
        $ca = $this->getYearTotal($year, $devise, $serviceId);
        $marge = $this->getYearTotal($year, $devise, $serviceId, 'MARGE');
        
        // Dépenses
        $depensesQuery = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        });
        
        if ($serviceId) {
            $depensesQuery->where('id_service', $serviceId);
        }
        $totalDepenses = $depensesQuery->sum("montant_{$devise}");
        
        // Investissements
        $investQuery = FinanceFait::whereHas('activite', function($q) {
            $q->where('type_activite', 'investissement');
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        });
        
        if ($serviceId) {
            $investQuery->where('id_service', $serviceId);
        }
        $investissements = $investQuery->sum("montant_{$devise}");
        
        // ROI estimé
        $roi = $investissements > 0 ? round(($marge / $investissements) * 100, 1) : 0;
        
        return [
            'marge_nette' => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
            'ratio_depenses' => $ca > 0 ? round($totalDepenses / $ca * 100, 1) : 0,
            'rentabilite' => $ca > 0 ? round(($marge - $totalDepenses) / $ca * 100, 1) : 0,
            'roi' => $roi,
            'investissements' => $investissements,
            'depenses_total' => $totalDepenses
        ];
    }
    
    /**
     * Données de tendance
     */
    private function getTrendData($devise, $period)
    {
        $data = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now();
        
        switch ($period) {
            case 'month':
                $startDate->subMonths(12);
                $interval = 'month';
                $format = 'M Y';
                break;
            case 'quarter':
                $startDate->subQuarters(8);
                $interval = 'quarter';
                $format = '[T]Q Y';
                break;
            default:
                $startDate->subYears(5);
                $interval = 'year';
                $format = 'Y';
        }
        
        // Tendance CA
        $caTrend = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($startDate) {
            $q->where('date', '>=', $startDate);
        })
        ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as period"), DB::raw("SUM(montant_{$devise}) as total"))
        ->groupBy('period')
        ->orderBy('period')
        ->get();
        
        // Tendance Marge
        $margeTrend = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'MARGE');
        })->whereHas('temps', function($q) use ($startDate) {
            $q->where('date', '>=', $startDate);
        })
        ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as period"), DB::raw("SUM(montant_{$devise}) as total"))
        ->groupBy('period')
        ->orderBy('period')
        ->get();
        
        // Calcul de la droite de tendance (régression linéaire)
        $trendLine = $this->calculateTrendLine($caTrend->pluck('total')->toArray());
        
        return [
            'ca' => $caTrend,
            'marge' => $margeTrend,
            'trend_line' => $trendLine,
            'period' => $period
        ];
    }
    
    /**
     * Indicateurs de tendance
     */
    private function getTrendIndicators($devise)
    {
        // Moyenne mobile sur 3 mois
        $last3Months = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) {
            $q->where('mois', '>=', date('m') - 2)->where('annee', date('Y'));
        })->avg("montant_{$devise}");
        
        $previous3Months = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) {
            $q->where('mois', '<', date('m') - 2)->where('mois', '>=', date('m') - 5)->where('annee', date('Y'));
        })->avg("montant_{$devise}");
        
        // Croissance CAGR
        $caStart = $this->getYearTotal(date('Y') - 2, $devise);
        $caEnd = $this->getYearTotal(date('Y'), $devise);
        $cagr = $caStart > 0 ? round((pow($caEnd / $caStart, 1/2) - 1) * 100, 1) : 0;
        
        // Volatilité (écart-type)
        $monthlyValues = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyValues[] = $this->getMonthTotal(date('Y'), $i, $devise);
        }
        $volatility = $this->calculateVolatility($monthlyValues);
        
        return [
            'moving_average_3m' => round($last3Months, 2),
            'moving_average_trend' => $last3Months > $previous3Months ? 'up' : 'down',
            'cagr' => $cagr,
            'volatility' => $volatility,
            'stability' => $volatility < 10 ? 'stable' : ($volatility < 20 ? 'moderate' : 'volatile')
        ];
    }
    
    /**
     * Projections
     */
    private function getProjections($devise)
    {
        // Projection linéaire pour les 12 prochains mois
        $historicalData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $value = $this->getMonthTotal($year, $month, $devise);
            $historicalData[] = $value;
        }
        
        $projection = $this->linearProjection($historicalData, 12);
        
        // Intervalle de confiance
        $stdDev = $this->calculateStdDev($historicalData);
        $confidenceInterval = [
            'lower' => array_map(function($v) use ($stdDev) { return max(0, $v - $stdDev); }, $projection),
            'upper' => array_map(function($v) use ($stdDev) { return $v + $stdDev; }, $projection)
        ];
        
        return [
            'months' => ['M+1', 'M+2', 'M+3', 'M+4', 'M+5', 'M+6', 'M+7', 'M+8', 'M+9', 'M+10', 'M+11', 'M+12'],
            'projection' => $projection,
            'confidence_lower' => $confidenceInterval['lower'],
            'confidence_upper' => $confidenceInterval['upper']
        ];
    }
    
    /**
     * Saisonnalité
     */
    private function getSeasonality($devise)
    {
        $seasonalIndex = [];
        
        // Calculer la moyenne par mois sur 3 ans
        for ($month = 1; $month <= 12; $month++) {
            $total = 0;
            $count = 0;
            
            for ($year = date('Y') - 2; $year <= date('Y'); $year++) {
                $value = $this->getMonthTotal($year, $month, $devise);
                if ($value > 0) {
                    $total += $value;
                    $count++;
                }
            }
            
            $average = $count > 0 ? $total / $count : 0;
            $seasonalIndex[] = [
                'month' => Carbon::createFromDate(null, $month, 1)->translatedFormat('F'),
                'average' => $average,
                'index' => 0 // Sera calculé après avoir la moyenne globale
            ];
        }
        
        // Calculer l'indice saisonnier
        $globalAverage = array_sum(array_column($seasonalIndex, 'average')) / 12;
        foreach ($seasonalIndex as &$item) {
            $item['index'] = $globalAverage > 0 ? round(($item['average'] / $globalAverage) * 100, 1) : 0;
        }
        
        // Trouver les pics et creux
        $max = max(array_column($seasonalIndex, 'average'));
        $min = min(array_column($seasonalIndex, 'average'));
        
        return [
            'data' => $seasonalIndex,
            'peak_month' => $seasonalIndex[array_search($max, array_column($seasonalIndex, 'average'))]['month'],
            'low_month' => $seasonalIndex[array_search($min, array_column($seasonalIndex, 'average'))]['month']
        ];
    }
    
    /**
     * Prévisions CA
     */
    private function getCAForecast($devise, $months)
    {
        // Méthode de lissage exponentiel
        $historical = [];
        for ($i = 0; $i < 24; $i++) {
            $date = Carbon::now()->subMonths($i);
            $value = $this->getMonthTotal($date->year, $date->month, $devise);
            $historical[] = $value;
        }
        
        $historical = array_reverse($historical);
        $forecast = $this->exponentialSmoothing($historical, $months, 0.3);
        
        return [
            'values' => $forecast,
            'months' => $this->getFutureMonths($months)
        ];
    }
    
    /**
     * Prévisions Marge
     */
    private function getMargeForecast($devise, $months)
    {
        $historical = [];
        for ($i = 0; $i < 24; $i++) {
            $date = Carbon::now()->subMonths($i);
            $value = $this->getMonthTotal($date->year, $date->month, $devise, 'MARGE');
            $historical[] = $value;
        }
        
        $historical = array_reverse($historical);
        $forecast = $this->exponentialSmoothing($historical, $months, 0.3);
        
        return [
            'values' => $forecast,
            'months' => $this->getFutureMonths($months)
        ];
    }
    
    /**
     * Scénarios
     */
    private function getScenarios($devise)
    {
        $currentCA = $this->getYearTotal(date('Y'), $devise);
        
        return [
            'optimistic' => [
                'label' => 'Optimiste (+20%)',
                'value' => round($currentCA * 1.2, 2),
                'color' => '#0e9f6e'
            ],
            'realistic' => [
                'label' => 'Réaliste (+8%)',
                'value' => round($currentCA * 1.08, 2),
                'color' => '#1B6EF3'
            ],
            'pessimistic' => [
                'label' => 'Pessimiste (-5%)',
                'value' => round($currentCA * 0.95, 2),
                'color' => '#e02424'
            ]
        ];
    }
    
    /**
     * Analyse de sensibilité
     */
    private function getSensitivityAnalysis($devise)
    {
        $baseCA = $this->getYearTotal(date('Y'), $devise);
        $baseMarge = $this->getYearTotal(date('Y'), $devise, null, 'MARGE');
        
        $scenarios = [];
        $variations = [-20, -10, -5, 0, 5, 10, 20];
        
        foreach ($variations as $var) {
            $newCA = $baseCA * (1 + $var / 100);
            $newMarge = $baseMarge * (1 + $var / 100);
            
            $scenarios[] = [
                'variation' => $var,
                'ca' => $newCA,
                'marge' => $newMarge,
                'impact' => $var
            ];
        }
        
        return $scenarios;
    }
    
    // ========== FONCTIONS UTILITAIRES ==========
    
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
    
    private function getMonthTotal($year, $month, $devise, $serviceId = null, $kpi = 'CA')
    {
        $query = FinanceFait::whereHas('indicateur', function($q) use ($kpi) {
            $q->where('code_kpi', $kpi);
        })->whereHas('temps', function($q) use ($year, $month) {
            $q->where('annee', $year)->where('mois', $month);
        });
        
        if ($serviceId) {
            $query->where('id_service', $serviceId);
        }
        
        return $query->sum("montant_{$devise}");
    }
    
    private function calculateTrendLine($data)
    {
        $n = count($data);
        if ($n < 2) return ['slope' => 0, 'intercept' => 0];
        
        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($data);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $data));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        return ['slope' => round($slope, 2), 'intercept' => round($intercept, 2)];
    }
    
    private function calculateVolatility($data)
    {
        $n = count($data);
        if ($n < 2) return 0;
        
        $mean = array_sum($data) / $n;
        $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $data)) / $n;
        
        return round(sqrt($variance), 2);
    }
    
    private function calculateStdDev($data)
    {
        $n = count($data);
        if ($n < 2) return 0;
        
        $mean = array_sum($data) / $n;
        $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $data)) / ($n - 1);
        
        return round(sqrt($variance), 2);
    }
    
    private function linearProjection($data, $steps)
    {
        $trend = $this->calculateTrendLine($data);
        $lastIndex = count($data);
        $projection = [];
        
        for ($i = 1; $i <= $steps; $i++) {
            $projection[] = round($trend['intercept'] + $trend['slope'] * ($lastIndex + $i), 2);
        }
        
        return $projection;
    }
    
    private function exponentialSmoothing($data, $steps, $alpha)
    {
        $smoothed = [];
        $last = $data[count($data) - 1];
        
        for ($i = 1; $i <= $steps; $i++) {
            $forecast = $alpha * $last + (1 - $alpha) * ($data[count($data) - 1] ?? $last);
            $smoothed[] = round($forecast, 2);
            $last = $forecast;
        }
        
        return $smoothed;
    }
    
    private function getFutureMonths($count)
    {
        $months = [];
        $current = Carbon::now();
        
        for ($i = 1; $i <= $count; $i++) {
            $months[] = $current->copy()->addMonths($i)->translatedFormat('M Y');
        }
        
        return $months;
    }
}