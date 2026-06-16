<?php
// app/Http/Controllers/Service/PerformanceController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $service = $user->service;
        
        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service associé');
        }
        
        $devise = session('devise', 'USD');
        $currentYear = date('Y');
        
        // 🔥 STATISTIQUES PRINCIPALES
        $caTotal = FinanceFait::where('id_service', $service->id_service)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        $margeTotal = FinanceFait::where('id_service', $service->id_service)
            ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        $depensesTotal = FinanceFait::where('id_service', $service->id_service)
            ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
            ->whereHas('temps', function($q) use ($currentYear) {
                $q->where('annee', $currentYear);
            })
            ->sum("montant_{$devise}");
        
        // 🔥 OBJECTIFS
        $budgetService = $service->budget_annuel ?? 0;
        $objectifs = [
            'ca' => [
                'actuel' => $caTotal,
                'objectif' => $budgetService,
                'progression' => $budgetService > 0 ? round(($caTotal / $budgetService) * 100, 1) : 0
            ],
            'marge' => [
                'actuel' => $margeTotal,
                'objectif' => $budgetService * 0.25,
                'progression' => $budgetService > 0 ? round(($margeTotal / ($budgetService * 0.25)) * 100, 1) : 0
            ]
        ];
        
        // 🔥 ÉVOLUTION MENSUELLE
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $ca = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($i, $currentYear) {
                    $q->where('mois', $i)->where('annee', $currentYear);
                })
                ->sum("montant_{$devise}");
            $monthlyData[] = $ca;
        }
        
        return view('service.performances.index', compact(
            'service',
            'caTotal',
            'margeTotal',
            'depensesTotal',
            'objectifs',
            'monthlyData',
            'devise'
        ));
    }
}