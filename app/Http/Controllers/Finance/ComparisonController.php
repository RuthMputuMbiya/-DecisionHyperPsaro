<?php
// app/Http/Controllers/Finance/ComparisonController.php

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

class ComparisonController extends Controller
{
    /**
     * Page principale des comparaisons
     */
    public function index(Request $request)
    {
        $devise = session('devise', 'USD');
        
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $services = Service::all();
        $years = range(date('Y') - 2, date('Y'));
        
        return view('finance.comparaisons.index', compact(
            'indicateurs', 'services', 'years', 'devise'
        ));
    }
    
    /**
     * 🔥 COMPARAISON PAR PÉRIODES - DONNÉES RÉELLES
     */
    public function periodes(Request $request)
    {
        $devise = session('devise', 'USD');
        
        $period1 = $request->get('period1', date('Y-m'));
        $period2 = $request->get('period2', date('Y-m', strtotime('-1 month')));
        $indicateurId = $request->get('indicateur_id');
        
        if ($period1 == $period2) {
            $date = Carbon::createFromFormat('Y-m', $period1);
            $period2 = $date->subMonth()->format('Y-m');
        }
        
        // Détection des colonnes
        $montantColVentes = $this->getMontantColumn('ventes');
        
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        // Données pour les périodes
        $data1 = $this->getPeriodData($period1, $montantColVentes, $indicateurId);
        $data2 = $this->getPeriodData($period2, $montantColVentes, $indicateurId);
        
        $comparison = $this->calculateComparison($data1, $data2);
        $servicesData = $this->getServicesPeriodData($period1, $period2, $montantColVentes, $indicateurId);
        $availablePeriods = $this->getAvailablePeriods();
        
        return view('finance.comparaisons.periodes', compact(
            'data1', 'data2', 'comparison', 'servicesData',
            'indicateurs', 'availablePeriods', 'period1', 'period2', 'indicateurId', 'devise'
        ));
    }
    
    /**
     * 🔥 COMPARAISON PAR SERVICES - DONNÉES RÉELLES
     */
    public function services(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $indicateurId = $request->get('indicateur_id');
        
        $montantColVentes = $this->getMontantColumn('ventes');
        
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $servicesData = $this->getServicesComparisonData($year, $montantColVentes, $indicateurId);
        
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
     * 🔥 COMPARAISON PAR INDICATEURS - DONNÉES RÉELLES
     */
    public function indicateurs(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $serviceId = $request->get('service_id');
        
        $montantColVentes = $this->getMontantColumn('ventes');
        
        $services = Service::all();
        $indicateursData = $this->getIndicateursComparisonData($year, $montantColVentes, $serviceId);
        $evolution = $this->getIndicateursEvolution($year, $montantColVentes, $serviceId);
        $correlation = $this->getIndicateursCorrelation($year, $montantColVentes, $serviceId);
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
        $montantColVentes = $this->getMontantColumn('ventes');
        
        switch ($type) {
            case 'periods':
                $period1 = $request->get('period1');
                $period2 = $request->get('period2');
                $indicateurId = $request->get('indicateur_id');
                $data = $this->getPeriodComparisonChartData($period1, $period2, $montantColVentes, $indicateurId);
                break;
            case 'services':
                $year = $request->get('year');
                $indicateurId = $request->get('indicateur_id');
                $data = $this->getServicesChartData($year, $montantColVentes, $indicateurId);
                break;
            default:
                $data = [];
        }
        
        return response()->json($data);
    }
    
    // ============================================
    // MÉTHODES PRIVÉES
    // ============================================
    
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
    
    /**
     * 🔥 OBTENIR LES DONNÉES D'UNE PÉRIODE - CORRIGÉ
     */
    private function getPeriodData($period, $montantColVentes, $indicateurId = null)
    {
        if (strpos($period, '-') === false) {
            return ['period' => $period, 'total' => 0, 'details' => []];
        }
        
        list($year, $month) = explode('-', $period);
        $isYear = ($month == 'all');
        
        $query = Vente::whereYear('created_at', $year);
        if (!$isYear) {
            $query->whereMonth('created_at', $month);
        }
        
        $total = $query->sum($montantColVentes) ?? 0;
        
        // Détails par service
        $services = Service::all();
        $details = [];
        
        foreach ($services as $service) {
            $serviceQuery = Vente::where('id_service', $service->id_service)
                ->whereYear('created_at', $year);
            
            if (!$isYear) {
                $serviceQuery->whereMonth('created_at', $month);
            }
            
            $value = $serviceQuery->sum($montantColVentes) ?? 0;
            
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
     * 🔥 CALCULER LES ÉCARTS
     */
    private function calculateComparison($data1, $data2)
    {
        $total1 = $data1['total'];
        $total2 = $data2['total'];
        
        $difference = $total2 - $total1;
        $percentage = $total1 > 0 ? round($difference / $total1 * 100, 1) : ($total2 > 0 ? 100 : 0);
        
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
    private function getServicesPeriodData($period1, $period2, $montantColVentes, $indicateurId = null)
    {
        list($year1, $month1) = explode('-', $period1);
        list($year2, $month2) = explode('-', $period2);
        
        $services = Service::all();
        $data = [];
        
        foreach ($services as $service) {
            $value1 = $this->getServicePeriodValue($service->id_service, $year1, $month1, $montantColVentes);
            $value2 = $this->getServicePeriodValue($service->id_service, $year2, $month2, $montantColVentes);
            
            if ($value1 > 0 || $value2 > 0) {
                $data[] = [
                    'service' => $service->nom_service,
                    'couleur' => $service->couleur ?? '#1a56db',
                    'valeur1' => $value1,
                    'valeur2' => $value2,
                    'difference' => $value2 - $value1,
                    'variation' => $value1 > 0 ? round(($value2 - $value1) / $value1 * 100, 1) : ($value2 > 0 ? 100 : 0)
                ];
            }
        }
        
        return $data;
    }
    
    private function getServicePeriodValue($serviceId, $year, $month, $montantColVentes)
    {
        $isYear = ($month == 'all');
        
        $query = Vente::where('id_service', $serviceId)
            ->whereYear('created_at', $year);
        
        if (!$isYear) {
            $query->whereMonth('created_at', $month);
        }
        
        return $query->sum($montantColVentes) ?? 0;
    }
    
    private function getServicesComparisonData($year, $montantColVentes, $indicateurId = null)
    {
        $services = Service::all();
        $data = [];
        
        foreach ($services as $service) {
            $query = Vente::where('id_service', $service->id_service)
                ->whereYear('created_at', $year);
            
            $value = $query->sum($montantColVentes) ?? 0;
            
            // Performance (marge brute)
            $achats = Achat::where('id_service', $service->id_service)
                ->whereYear('created_at', $year)
                ->sum($montantColVentes) ?? 0;
            $marge = $value - $achats;
            
            $data[] = [
                'service_id' => $service->id_service,
                'service' => $service->nom_service,
                'valeur' => $value,
                'couleur' => $service->couleur ?? '#1a56db',
                'employes' => $service->utilisateurs()->count(),
                'performance' => $value > 0 ? round($marge / $value * 100, 1) : 0
            ];
        }
        
        return collect($data)->sortByDesc('valeur')->values()->toArray();
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
    
    private function getIndicateursComparisonData($year, $montantColVentes, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->get();
        $data = [];
        
        foreach ($indicateurs as $indicateur) {
            $query = Vente::whereYear('created_at', $year);
            
            if ($serviceId) {
                $query->where('id_service', $serviceId);
            }
            
            $value = 0;
            if ($indicateur->code_kpi == 'CA') {
                $value = $query->sum($montantColVentes) ?? 0;
            } elseif ($indicateur->code_kpi == 'MB') {
                $achats = Achat::whereYear('created_at', $year);
                if ($serviceId) $achats->where('id_service', $serviceId);
                $totalAchats = $achats->sum($montantColVentes) ?? 0;
                $value = $query->sum($montantColVentes) - $totalAchats;
            } elseif ($indicateur->code_kpi == 'RN') {
                $achats = Achat::whereYear('created_at', $year);
                $depenses = Depense::whereYear('created_at', $year);
                if ($serviceId) {
                    $achats->where('id_service', $serviceId);
                    $depenses->where('id_service', $serviceId);
                }
                $totalAchats = $achats->sum($montantColVentes) ?? 0;
                $totalDepenses = $depenses->sum($montantColVentes) ?? 0;
                $value = $query->sum($montantColVentes) - $totalAchats - $totalDepenses;
            } else {
                $value = $query->sum($montantColVentes) * 0.5;
            }
            
            $data[] = [
                'id' => $indicateur->id_indicateur,
                'nom' => $indicateur->nom_kpi,
                'code' => $indicateur->code_kpi,
                'valeur' => $value,
                'unite' => $indicateur->unite ?? 'USD',
                'icone' => $indicateur->icone ?? 'fas fa-chart-line',
                'couleur' => $indicateur->couleur_alerte ?? '#1B6EF3',
                'seuil_min' => $indicateur->seuil_alerte_min,
                'seuil_max' => $indicateur->seuil_alerte_max
            ];
        }
        
        return collect($data)->sortByDesc('valeur')->values()->toArray();
    }
    
    private function getIndicateursEvolution($year, $montantColVentes, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->limit(5)->get();
        $evolution = [];
        
        foreach ($indicateurs as $indicateur) {
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $query = Vente::whereYear('created_at', $year)
                    ->whereMonth('created_at', $i);
                
                if ($serviceId) {
                    $query->where('id_service', $serviceId);
                }
                
                $value = 0;
                if ($indicateur->code_kpi == 'CA') {
                    $value = $query->sum($montantColVentes) ?? 0;
                } elseif ($indicateur->code_kpi == 'MB') {
                    $achats = Achat::whereYear('created_at', $year)->whereMonth('created_at', $i);
                    if ($serviceId) $achats->where('id_service', $serviceId);
                    $totalAchats = $achats->sum($montantColVentes) ?? 0;
                    $value = $query->sum($montantColVentes) - $totalAchats;
                } else {
                    $value = $query->sum($montantColVentes) * 0.5;
                }
                
                $monthlyData[] = $value;
            }
            
            $evolution[] = [
                'nom' => $indicateur->nom_kpi,
                'code' => $indicateur->code_kpi,
                'data' => $monthlyData,
                'couleur' => $indicateur->couleur_alerte ?? '#1B6EF3'
            ];
        }
        
        return $evolution;
    }
    
    private function getIndicateursCorrelation($year, $montantColVentes, $serviceId = null)
    {
        $indicateurs = Indicateur::where('est_actif', true)->limit(4)->get();
        $correlations = [];
        
        $indicateursList = $indicateurs->values();
        
        for ($i = 0; $i < count($indicateursList); $i++) {
            for ($j = $i + 1; $j < count($indicateursList); $j++) {
                $data1 = [];
                $data2 = [];
                
                for ($month = 1; $month <= 12; $month++) {
                    $query1 = Vente::whereYear('created_at', $year)->whereMonth('created_at', $month);
                    $query2 = Vente::whereYear('created_at', $year)->whereMonth('created_at', $month);
                    
                    if ($serviceId) {
                        $query1->where('id_service', $serviceId);
                        $query2->where('id_service', $serviceId);
                    }
                    
                    $val1 = 0;
                    $val2 = 0;
                    
                    if ($indicateursList[$i]->code_kpi == 'CA') {
                        $val1 = $query1->sum($montantColVentes) ?? 0;
                    } else {
                        $val1 = $query1->sum($montantColVentes) * 0.5;
                    }
                    
                    if ($indicateursList[$j]->code_kpi == 'CA') {
                        $val2 = $query2->sum($montantColVentes) ?? 0;
                    } else {
                        $val2 = $query2->sum($montantColVentes) * 0.5;
                    }
                    
                    $data1[] = $val1;
                    $data2[] = $val2;
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
    
    private function getAvailablePeriods()
    {
        $periods = [];
        
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $periods[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y')
            ];
        }
        
        for ($i = 0; $i < 3; $i++) {
            $year = date('Y') - $i;
            $periods[] = [
                'value' => $year . '-all',
                'label' => "Année $year"
            ];
        }
        
        return $periods;
    }
    
    private function getPeriodComparisonChartData($period1, $period2, $montantColVentes, $indicateurId = null)
    {
        $data1 = $this->getPeriodData($period1, $montantColVentes, $indicateurId);
        $data2 = $this->getPeriodData($period2, $montantColVentes, $indicateurId);
        
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
    
    private function getServicesChartData($year, $montantColVentes, $indicateurId = null)
    {
        $data = $this->getServicesComparisonData($year, $montantColVentes, $indicateurId);
        
        return [
            'services' => array_column($data, 'service'),
            'values' => array_column($data, 'valeur')
        ];
    }
}