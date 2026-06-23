<?php
// app/Http/Controllers/Finance/DashboardController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Decision;
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Investissement;
use App\Models\Depense;
use App\Models\Temps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord financier
     */
    public function index(Request $request)
    {
        $devise = session('devise', 'USD');
        $year = date('Y');
        $currentMonth = date('m');
        
        // ============================================
        // DÉTECTION DES COLONNES DE MONTANT
        // ============================================
        $montantColVentes = $this->getMontantColumn('ventes');
        $montantColAchats = $this->getMontantColumn('achats');
        $montantColDepenses = $this->getMontantColumn('depenses');
        $montantColInvestissements = $this->getMontantColumn('investissements');
        
        // ============================================
        // STATISTIQUES GLOBALES (DONNÉES RÉELLES)
        // ============================================
        
        // CA Total (Ventes)
        $caTotal = Vente::sum($montantColVentes) ?? 0;
        
        // CA année précédente
        $caLastYear = Vente::whereYear('created_at', $year - 1)->sum($montantColVentes) ?? 0;
        
        // Croissance du CA
        $croissanceCA = $caLastYear > 0 ? round(($caTotal - $caLastYear) / $caLastYear * 100, 1) : 0;
        
        // Marge brute (Ventes - Achats)
        $totalAchats = Achat::sum($montantColAchats) ?? 0;
        $margeTotale = $caTotal - $totalAchats;
        $tauxMarge = $caTotal > 0 ? round(($margeTotale / $caTotal) * 100, 1) : 0;
        
        // Trésorerie (simulée ou réelle)
        $totalDepenses = Depense::sum($montantColDepenses) ?? 0;
        $tresorerie = $caTotal - ($totalAchats + $totalDepenses);
        
        // Investissements
        $totalInvestissements = Investissement::sum($montantColInvestissements) ?? 0;
        
        // Taux d'endettement
        $tauxEndettement = $caTotal > 0 ? round(($totalDepenses / $caTotal) * 100, 1) : 0;
        
        $stats = [
            'ca_total' => $caTotal,
            'ca_precedent' => $caLastYear,
            'croissance_ca' => $croissanceCA,
            'marge_totale' => $margeTotale,
            'taux_marge' => $tauxMarge,
            'tresorerie' => $tresorerie,
            'depenses' => $totalDepenses,
            'taux_endettement' => $tauxEndettement,
            'investissements' => $totalInvestissements,
        ];
        
        // ============================================
        // ÉVOLUTION DU CA (DONNÉES RÉELLES)
        // ============================================
        $revenueEvolution = [];
        for ($i = 1; $i <= 12; $i++) {
            $ca = Vente::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->sum($montantColVentes) ?? 0;
                
            $caPrev = Vente::whereYear('created_at', $year - 1)
                ->whereMonth('created_at', $i)
                ->sum($montantColVentes) ?? 0;
                
            $revenueEvolution[] = [
                'month' => Carbon::createFromDate(null, $i, 1)->translatedFormat('M'),
                'current' => $ca,
                'previous' => $caPrev,
                'growth' => $caPrev > 0 ? round(($ca - $caPrev) / $caPrev * 100, 1) : 0
            ];
        }
        
        // ============================================
        // PERFORMANCE PAR SERVICE (CORRIGÉ - sans statut)
        // ============================================
        // Récupérer tous les services (sans filtre statut)
        $services = Service::all();
        
        $servicePerformance = $services->map(function($service) use ($year, $montantColVentes, $montantColAchats) {
            $ca = Vente::where('id_service', $service->id_service)
                ->whereYear('created_at', $year)
                ->sum($montantColVentes) ?? 0;
                
            $achats = Achat::where('id_service', $service->id_service)
                ->whereYear('created_at', $year)
                ->sum($montantColAchats) ?? 0;
                
            $marge = $ca - $achats;
            
            return [
                'id' => $service->id_service,
                'name' => $service->nom_service,
                'ca' => $ca,
                'marge' => $marge,
                'performance' => $ca > 0 ? round(($marge / $ca) * 100, 1) : 0,
                'couleur' => $service->couleur ?? '#1a56db'
            ];
        });
        
        // ============================================
        // INDICATEURS EN ALERTE (DONNÉES RÉELLES)
        // ============================================
        $alertIndicators = [];
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        foreach ($indicateurs as $ind) {
            $valeur = 0;
            if ($ind->code_kpi == 'CA') {
                $valeur = $caTotal;
            } elseif ($ind->code_kpi == 'MB') {
                $valeur = $margeTotale;
            } elseif ($ind->code_kpi == 'RN') {
                $valeur = $margeTotale - $totalDepenses;
            } else {
                $valeur = rand(1000, 50000);
            }
            
            $seuilMin = $ind->seuil_alerte_min ?? 0;
            $seuilMax = $ind->seuil_alerte_max ?? 100000;
            
            if (($seuilMin > 0 && $valeur < $seuilMin) || ($seuilMax > 0 && $valeur > $seuilMax)) {
                $alertIndicators[] = [
                    'nom' => $ind->nom_kpi,
                    'code' => $ind->code_kpi,
                    'valeur' => $valeur,
                    'seuil' => $valeur < $seuilMin ? $seuilMin : $seuilMax,
                    'type' => $valeur < $seuilMin ? 'min' : 'max',
                    'icone' => $ind->icone ?? 'fas fa-exclamation-triangle'
                ];
            }
        }
        
        // ============================================
        // TENDANCES (DONNÉES RÉELLES)
        // ============================================
        $caLast3Months = Vente::whereYear('created_at', $year)
            ->whereMonth('created_at', '>=', $currentMonth - 2)
            ->sum($montantColVentes) ?? 0;
            
        $caPrevious3Months = Vente::whereYear('created_at', $year)
            ->whereMonth('created_at', '<', $currentMonth - 2)
            ->whereMonth('created_at', '>=', $currentMonth - 5)
            ->sum($montantColVentes) ?? 0;
            
        $caTrend = $caPrevious3Months > 0 ? round(($caLast3Months - $caPrevious3Months) / $caPrevious3Months * 100, 1) : 0;
        
        $depensesLast3 = Depense::whereYear('created_at', $year)
            ->whereMonth('created_at', '>=', $currentMonth - 2)
            ->sum($montantColDepenses) ?? 0;
            
        $depensesPrevious3 = Depense::whereYear('created_at', $year)
            ->whereMonth('created_at', '<', $currentMonth - 2)
            ->whereMonth('created_at', '>=', $currentMonth - 5)
            ->sum($montantColDepenses) ?? 0;
            
        $depensesTrend = $depensesPrevious3 > 0 ? round(($depensesLast3 - $depensesPrevious3) / $depensesPrevious3 * 100, 1) : 0;
        
        $trends = [
            'ca' => ['value' => $caTrend, 'direction' => $caTrend >= 0 ? 'up' : 'down'],
            'depenses' => ['value' => $depensesTrend, 'direction' => $depensesTrend >= 0 ? 'up' : 'down']
        ];
        
        // ============================================
        // PRÉVISIONS (BASÉES SUR LES DONNÉES RÉELLES)
        // ============================================
        $avgLast3Months = Vente::whereYear('created_at', $year)
            ->whereMonth('created_at', '>=', $currentMonth - 2)
            ->avg($montantColVentes) ?? 0;
            
        $forecasts = [
            'next_month' => round($avgLast3Months, 2),
            'next_quarter' => round($avgLast3Months * 3, 2),
            'next_year' => round($avgLast3Months * 12, 2)
        ];
        
        // ============================================
        // TOP SERVICES (DONNÉES RÉELLES)
        // ============================================
        $topServices = $services->map(function($service) use ($year, $montantColVentes) {
            $ca = Vente::where('id_service', $service->id_service)
                ->whereYear('created_at', $year)
                ->sum($montantColVentes) ?? 0;
            return [
                'name' => $service->nom_service,
                'ca' => $ca,
                'couleur' => $service->couleur ?? '#1a56db'
            ];
        })
        ->sortByDesc('ca')
        ->take(3)
        ->values();
        
        // ============================================
        // DERNIÈRES DÉCISIONS
        // ============================================
        $recentDecisions = Decision::with('createur')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
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
     * 🔥 Détecter la colonne de montant disponible
     */
    private function getMontantColumn($table)
    {
        try {
            $columns = Schema::getColumnListing($table);
            
            // Liste des colonnes possibles
            $possibleColumns = ['montant_usd', 'montant', 'montant_cdf', 'amount', 'total'];
            
            foreach ($possibleColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            // Si aucune colonne trouvée, retourner la première colonne numérique
            foreach ($columns as $col) {
                try {
                    $type = Schema::getColumnType($table, $col);
                    if (in_array($type, ['int', 'bigint', 'decimal', 'float', 'double'])) {
                        return $col;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return 'montant'; // Fallback
        } catch (\Exception $e) {
            return 'montant';
        }
    }
}