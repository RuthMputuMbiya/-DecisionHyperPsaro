<?php
// app/Http/Controllers/Finance/AnalyseController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Depense;
use App\Models\Investissement;
use App\Models\Indicateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        
        // Détection des colonnes de montant
        $montantColVentes = $this->getMontantColumn('ventes');
        $montantColAchats = $this->getMontantColumn('achats');
        $montantColDepenses = $this->getMontantColumn('depenses');
        
        // Données pour l'analyse
        $monthlyData = $this->getMonthlyAnalysis($montantColVentes, $montantColAchats, $year, $serviceId);
        
        // Comparaison avec année précédente
        $yearComparison = $this->getYearComparison($montantColVentes, $year, $serviceId);
        
        // Ratios financiers
        $ratios = $this->getFinancialRatios($montantColVentes, $montantColAchats, $montantColDepenses, $year, $serviceId);
        
        // Services disponibles
        $services = Service::all();
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
        $period = $request->get('period', 'month');
        
        $montantColVentes = $this->getMontantColumn('ventes');
        $montantColAchats = $this->getMontantColumn('achats');
        
        // Données de tendance
        $trendData = $this->getTrendData($montantColVentes, $period);
        
        // Indicateurs de tendance
        $indicators = $this->getTrendIndicators($montantColVentes);
        
        // Projections
        $projections = $this->getProjections($montantColVentes);
        
        // Saisonnalité
        $seasonality = $this->getSeasonality($montantColVentes);
        
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
        
        $montantColVentes = $this->getMontantColumn('ventes');
        $montantColAchats = $this->getMontantColumn('achats');
        
        // Prévisions CA
        $caForecast = $this->getCAForecast($montantColVentes, $months);
        
        // Prévisions Marge
        $margeForecast = $this->getMargeForecast($montantColVentes, $montantColAchats, $months);
        
        // Scénarios
        $scenarios = $this->getScenarios($montantColVentes);
        
        // Analyse de sensibilité
        $sensitivity = $this->getSensitivityAnalysis($montantColVentes);
        
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
     * 🔥 Analyse mensuelle avec données réelles
     */
    private function getMonthlyAnalysis($montantColVentes, $montantColAchats, $year, $serviceId = null)
    {
        $data = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $query = Vente::whereYear('created_at', $year)->whereMonth('created_at', $i);
            if ($serviceId) {
                $query->where('id_service', $serviceId);
            }
            $ca = $query->sum($montantColVentes) ?? 0;
            
            $queryAchats = Achat::whereYear('created_at', $year)->whereMonth('created_at', $i);
            if ($serviceId) {
                $queryAchats->where('id_service', $serviceId);
            }
            $achats = $queryAchats->sum($montantColAchats) ?? 0;
            
            $marge = $ca - $achats;
            
            // Variation par rapport au mois précédent
            $prevMonth = $i == 1 ? 12 : $i - 1;
            $prevYear = $i == 1 ? $year - 1 : $year;
            
            $prevCa = Vente::whereYear('created_at', $prevYear)->whereMonth('created_at', $prevMonth);
            if ($serviceId) {
                $prevCa->where('id_service', $serviceId);
            }
            $prevCaValue = $prevCa->sum($montantColVentes) ?? 0;
            
            $variation = $prevCaValue > 0 ? round(($ca - $prevCaValue) / $prevCaValue * 100, 1) : 0;
            
            $data[] = [
                'month' => Carbon::createFromDate($year, $i, 1)->translatedFormat('F'),
                'month_num' => $i,
                'ca' => $ca,
                'achats' => $achats,
                'marge' => $marge,
                'taux_marge' => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
                'variation' => $variation,
                'variation_direction' => $variation >= 0 ? 'up' : 'down'
            ];
        }
        
        return $data;
    }
    
    /**
     * 🔥 Comparaison année
     */
    private function getYearComparison($montantColVentes, $year, $serviceId = null)
    {
        $currentYearTotal = $this->getYearTotal($montantColVentes, $year, $serviceId);
        $prevYearTotal = $this->getYearTotal($montantColVentes, $year - 1, $serviceId);
        $twoYearsAgoTotal = $this->getYearTotal($montantColVentes, $year - 2, $serviceId);
        
        $variation = $prevYearTotal > 0 ? round(($currentYearTotal - $prevYearTotal) / $prevYearTotal * 100, 1) : 0;
        $variation2 = $twoYearsAgoTotal > 0 ? round(($prevYearTotal - $twoYearsAgoTotal) / $twoYearsAgoTotal * 100, 1) : 0;
        
        // Évolution mensuelle comparée
        $monthlyComparison = [];
        for ($i = 1; $i <= 12; $i++) {
            $current = $this->getMonthTotal($montantColVentes, $year, $i, $serviceId);
            $prev = $this->getMonthTotal($montantColVentes, $year - 1, $i, $serviceId);
            
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
     * 🔥 Ratios financiers
     */
    private function getFinancialRatios($montantColVentes, $montantColAchats, $montantColDepenses, $year, $serviceId = null)
    {
        $ca = $this->getYearTotal($montantColVentes, $year, $serviceId);
        $achats = $this->getYearTotal($montantColAchats, $year, $serviceId);
        $marge = $ca - $achats;
        
        // Dépenses
        $queryDepenses = Depense::whereYear('created_at', $year);
        if ($serviceId) {
            $queryDepenses->where('id_service', $serviceId);
        }
        $totalDepenses = $queryDepenses->sum($montantColDepenses) ?? 0;
        
        // Investissements
        $queryInvest = Investissement::whereYear('created_at', $year);
        if ($serviceId) {
            $queryInvest->where('id_service', $serviceId);
        }
        $investissements = $queryInvest->sum('montant') ?? 0;
        
        // ROI estimé
        $roi = $investissements > 0 ? round(($marge / $investissements) * 100, 1) : 0;
        
        return [
            'marge_nette' => $ca > 0 ? round($marge / $ca * 100, 1) : 0,
            'ratio_depenses' => $ca > 0 ? round($totalDepenses / $ca * 100, 1) : 0,
            'rentabilite' => $ca > 0 ? round(($marge - $totalDepenses) / $ca * 100, 1) : 0,
            'roi' => $roi,
            'investissements' => $investissements,
            'depenses_total' => $totalDepenses,
            'ca_total' => $ca,
            'marge_total' => $marge
        ];
    }
    
    /**
     * 🔥 Données de tendance
     */
    private function getTrendData($montantColVentes, $period)
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
        
        // Tendance CA par mois
        $caTrend = Vente::where('created_at', '>=', $startDate)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"), DB::raw("SUM({$montantColVentes}) as total"))
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        return [
            'ca' => $caTrend,
            'period' => $period
        ];
    }
    
    /**
     * 🔥 Indicateurs de tendance
     */
    private function getTrendIndicators($montantColVentes)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Moyenne mobile sur 3 mois
        $last3Months = Vente::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', '>=', $currentMonth - 2)
            ->avg($montantColVentes) ?? 0;
            
        $previous3Months = Vente::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', '<', $currentMonth - 2)
            ->whereMonth('created_at', '>=', $currentMonth - 5)
            ->avg($montantColVentes) ?? 0;
        
        // Croissance CAGR
        $caStart = Vente::whereYear('created_at', $currentYear - 2)->sum($montantColVentes) ?? 0;
        $caEnd = Vente::whereYear('created_at', $currentYear)->sum($montantColVentes) ?? 0;
        $cagr = $caStart > 0 ? round((pow($caEnd / $caStart, 1/2) - 1) * 100, 1) : 0;
        
        // Volatilité
        $monthlyValues = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyValues[] = Vente::whereYear('created_at', $currentYear)->whereMonth('created_at', $i)->sum($montantColVentes) ?? 0;
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
     * 🔥 Projections
     */
    private function getProjections($montantColVentes)
    {
        $historicalData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $value = Vente::whereYear('created_at', $year)->whereMonth('created_at', $month)->sum($montantColVentes) ?? 0;
            $historicalData[] = $value;
        }
        
        $projection = $this->linearProjection($historicalData, 12);
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
     * 🔥 Saisonnalité
     */
    private function getSeasonality($montantColVentes)
    {
        $seasonalIndex = [];
        $currentYear = date('Y');
        
        for ($month = 1; $month <= 12; $month++) {
            $total = 0;
            $count = 0;
            
            for ($year = $currentYear - 2; $year <= $currentYear; $year++) {
                $value = Vente::whereYear('created_at', $year)->whereMonth('created_at', $month)->sum($montantColVentes) ?? 0;
                if ($value > 0) {
                    $total += $value;
                    $count++;
                }
            }
            
            $average = $count > 0 ? $total / $count : 0;
            $seasonalIndex[] = [
                'month' => Carbon::createFromDate(null, $month, 1)->translatedFormat('F'),
                'average' => $average,
                'index' => 0
            ];
        }
        
        $globalAverage = array_sum(array_column($seasonalIndex, 'average')) / 12;
        foreach ($seasonalIndex as &$item) {
            $item['index'] = $globalAverage > 0 ? round(($item['average'] / $globalAverage) * 100, 1) : 0;
        }
        
        $max = max(array_column($seasonalIndex, 'average'));
        $min = min(array_column($seasonalIndex, 'average'));
        
        return [
            'data' => $seasonalIndex,
            'peak_month' => $seasonalIndex[array_search($max, array_column($seasonalIndex, 'average'))]['month'],
            'low_month' => $seasonalIndex[array_search($min, array_column($seasonalIndex, 'average'))]['month']
        ];
    }
    
    /**
     * 🔥 Prévisions CA
     */
    private function getCAForecast($montantColVentes, $months)
    {
        $historical = [];
        for ($i = 0; $i < 24; $i++) {
            $date = Carbon::now()->subMonths($i);
            $value = Vente::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum($montantColVentes) ?? 0;
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
     * 🔥 Prévisions Marge
     */
    private function getMargeForecast($montantColVentes, $montantColAchats, $months)
    {
        $historical = [];
        for ($i = 0; $i < 24; $i++) {
            $date = Carbon::now()->subMonths($i);
            $ca = Vente::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum($montantColVentes) ?? 0;
            $achats = Achat::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->sum($montantColAchats) ?? 0;
            $marge = $ca - $achats;
            $historical[] = $marge;
        }
        
        $historical = array_reverse($historical);
        $forecast = $this->exponentialSmoothing($historical, $months, 0.3);
        
        return [
            'values' => $forecast,
            'months' => $this->getFutureMonths($months)
        ];
    }
    
    /**
     * 🔥 Scénarios
     */
    private function getScenarios($montantColVentes)
    {
        $currentCA = Vente::whereYear('created_at', date('Y'))->sum($montantColVentes) ?? 0;
        
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
     * 🔥 Analyse de sensibilité
     */
    private function getSensitivityAnalysis($montantColVentes)
    {
        $baseCA = Vente::whereYear('created_at', date('Y'))->sum($montantColVentes) ?? 0;
        
        $scenarios = [];
        $variations = [-20, -10, -5, 0, 5, 10, 20];
        
        foreach ($variations as $var) {
            $newCA = $baseCA * (1 + $var / 100);
            
            $scenarios[] = [
                'variation' => $var,
                'ca' => $newCA,
                'impact' => $var
            ];
        }
        
        return $scenarios;
    }
    
    // ========== FONCTIONS UTILITAIRES ==========
    
    /**
     * 🔥 Détecter la colonne de montant
     */
    private function getMontantColumn($table)
    {
        try {
            $columns = Schema::getColumnListing($table);
            $possibleColumns = ['montant_usd', 'montant', 'montant_cdf', 'amount', 'total'];
            
            foreach ($possibleColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            return 'montant';
        } catch (\Exception $e) {
            return 'montant';
        }
    }
    
    private function getYearTotal($montantCol, $year, $serviceId = null)
    {
        $query = Vente::whereYear('created_at', $year);
        if ($serviceId) {
            $query->where('id_service', $serviceId);
        }
        return $query->sum($montantCol) ?? 0;
    }
    
    private function getMonthTotal($montantCol, $year, $month, $serviceId = null)
    {
        $query = Vente::whereYear('created_at', $year)->whereMonth('created_at', $month);
        if ($serviceId) {
            $query->where('id_service', $serviceId);
        }
        return $query->sum($montantCol) ?? 0;
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
            $projection[] = round(max(0, $trend['intercept'] + $trend['slope'] * ($lastIndex + $i)), 2);
        }
        
        return $projection;
    }
    
    private function exponentialSmoothing($data, $steps, $alpha)
    {
        $smoothed = [];
        $last = $data[count($data) - 1] ?? 0;
        
        for ($i = 1; $i <= $steps; $i++) {
            $forecast = $alpha * $last + (1 - $alpha) * ($data[count($data) - 1] ?? $last);
            $smoothed[] = round(max(0, $forecast), 2);
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