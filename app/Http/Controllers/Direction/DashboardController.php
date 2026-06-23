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
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Depense;
use App\Models\Investissement;
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
        
        // Top services par CA
        $topServices = $this->getTopServices();
        
        return view('direction.dashboard', compact(
            'stats',
            'servicePerformances',
            'topServices'
        ));
    }
    
    private function getGlobalStats()
    {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        $devise = session('devise', 'USD');
        
        // 🔥 CA total depuis FinanceFait
        $totalCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}") ?? 0;
        
        // Si pas de CA, prendre depuis les ventes
        if ($totalCA == 0) {
            $totalCA = Vente::whereYear('created_at', $currentYear)->sum('montant') ?? 0;
        }
        
        // 🔥 CA année précédente
        $lastYearCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($lastYear) {
            $q->where('annee', $lastYear);
        })->sum("montant_{$devise}") ?? 0;
        
        if ($lastYearCA == 0) {
            $lastYearCA = Vente::whereYear('created_at', $lastYear)->sum('montant') ?? 0;
        }
        
        // 🔥 Marge totale
        $totalMarge = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'MARGE');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}") ?? 0;
        
        // Si pas de marge, la calculer
        if ($totalMarge == 0) {
            $ventes = Vente::whereYear('created_at', $currentYear)->sum('montant') ?? 0;
            $achats = Achat::whereYear('created_at', $currentYear)->sum('montant') ?? 0;
            $totalMarge = $ventes - $achats;
        }
        
        // 🔥 Trésorerie
        $tresorerie = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'TRESO');
        })->sum("montant_{$devise}") ?? 0;
        
        if ($tresorerie == 0) {
            $ventes = Vente::sum('montant') ?? 0;
            $achats = Achat::sum('montant') ?? 0;
            $depenses = Depense::sum('montant') ?? 0;
            $tresorerie = $ventes - $achats - $depenses;
        }
        
        // 🔥 Investissements
        $investissements = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'INVEST');
        })->whereHas('temps', function($q) use ($currentYear) {
            $q->where('annee', $currentYear);
        })->sum("montant_{$devise}") ?? 0;
        
        if ($investissements == 0) {
            $investissements = Investissement::whereYear('created_at', $currentYear)->sum('montant') ?? 0;
        }
        
        // 🔥 Taux de croissance CA
        $croissanceCA = $lastYearCA > 0 ? (($totalCA - $lastYearCA) / $lastYearCA) * 100 : 0;
        
        // 🔥 Marge brute en pourcentage
        $margePercent = $totalCA > 0 ? ($totalMarge / $totalCA) * 100 : 0;
        
        // 🔥 Services actifs
        $servicesActifs = Service::where('est_actif', true)->count();
        
        // 🔥 Utilisateurs actifs
        $utilisateursActifs = Utilisateur::where('est_actif', true)->count();
        
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
        $devise = session('devise', 'USD');
        
        $services = Service::where('est_actif', true)->get();
        $result = [];
        
        foreach ($services as $service) {
            // 🔥 CA du service
            $ca = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                })
                ->whereHas('temps', function($q) use ($currentYear) {
                    $q->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            if ($ca == 0) {
                $ca = Vente::where('id_service', $service->id_service)
                    ->whereYear('created_at', $currentYear)
                    ->sum('montant') ?? 0;
            }
            
            // 🔥 Marge du service
            $marge = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'MARGE');
                })
                ->whereHas('temps', function($q) use ($currentYear) {
                    $q->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            if ($marge == 0) {
                $ventes = Vente::where('id_service', $service->id_service)
                    ->whereYear('created_at', $currentYear)
                    ->sum('montant') ?? 0;
                $achats = Achat::where('id_service', $service->id_service)
                    ->whereYear('created_at', $currentYear)
                    ->sum('montant') ?? 0;
                $marge = $ventes - $achats;
            }
            
            $result[] = [
                'id' => $service->id_service,
                'name' => $service->nom_service,
                'ca' => $ca,
                'marge' => $marge,
                'performance' => $ca > 0 ? round(($marge / $ca) * 100, 1) : 0,
                'couleur' => $service->couleur ?? '#1a56db'
            ];
        }
        
        // Trier par CA décroissant
        usort($result, function($a, $b) {
            return $b['ca'] <=> $a['ca'];
        });
        
        return $result;
    }
    
    private function getTopServices()
    {
        $currentYear = date('Y');
        $devise = session('devise', 'USD');
        
        $services = Service::where('est_actif', true)->get();
        $result = [];
        
        foreach ($services as $service) {
            $total = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                })
                ->whereHas('temps', function($q) use ($currentYear) {
                    $q->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            if ($total == 0) {
                $total = Vente::where('id_service', $service->id_service)
                    ->whereYear('created_at', $currentYear)
                    ->sum('montant') ?? 0;
            }
            
            $result[] = [
                'name' => $service->nom_service,
                'total' => $total,
                'couleur' => $service->couleur ?? '#1a56db'
            ];
        }
        
        // Trier par total décroissant
        usort($result, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        return array_slice($result, 0, 5);
    }
}