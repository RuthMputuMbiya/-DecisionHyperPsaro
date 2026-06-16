<?php
// app/Http/Controllers/Direction/ReportController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Decision;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Afficher la page des rapports
     */
    public function index(Request $request)
    {
        $devise = session('devise', 'USD');
        
        // Liste des rapports disponibles
        $reports = [
            [
                'id' => 'financial_summary',
                'name' => 'Rapport financier synthétique',
                'description' => 'Vue d\'ensemble des performances financières de l\'entreprise',
                'icon' => 'fas fa-chart-line',
                'color' => '#1B6EF3'
            ],
            [
                'id' => 'kpi_dashboard',
                'name' => 'Tableau de bord des KPIs',
                'description' => 'Tous les indicateurs clés de performance',
                'icon' => 'fas fa-chart-pie',
                'color' => '#0e9f6e'
            ],
            [
                'id' => 'services_performance',
                'name' => 'Performance par service',
                'description' => 'Analyse détaillée des performances de chaque service',
                'icon' => 'fas fa-building',
                'color' => '#f5a623'
            ],
            [
                'id' => 'decisions_history',
                'name' => 'Historique des décisions',
                'description' => 'Toutes les décisions stratégiques avec leurs impacts',
                'icon' => 'fas fa-gavel',
                'color' => '#7e3af2'
            ],
            [
                'id' => 'monthly_report',
                'name' => 'Rapport mensuel',
                'description' => 'Rapport détaillé du mois en cours',
                'icon' => 'fas fa-calendar-alt',
                'color' => '#e02424'
            ],
            [
                'id' => 'annual_report',
                'name' => 'Rapport annuel',
                'description' => 'Bilan complet de l\'année fiscale',
                'icon' => 'fas fa-chart-simple',
                'color' => '#3abff8'
            ]
        ];
        
        $services = Service::where('est_actif', true)->get();
        $years = range(date('Y') - 2, date('Y'));
        
        return view('direction.reports.index', compact('reports', 'services', 'years', 'devise'));
    }
    
    /**
     * Générer un rapport PDF
     */
    public function generate(Request $request, $reportId)
    {
        $devise = session('devise', 'USD');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $serviceId = $request->get('service_id');
        
        $data = [
            'title' => $this->getReportTitle($reportId),
            'company_name' => 'HYPER PSARO',
            'generated_at' => now(),
            'generated_by' => auth()->user()->nom,
            'devise' => $devise,
            'year' => $year,
        ];
        
        switch ($reportId) {
            case 'financial_summary':
                $data = array_merge($data, $this->getFinancialSummaryData($devise, $year));
                $view = 'direction.reports.pdf.financial-summary';
                break;
            case 'kpi_dashboard':
                $data = array_merge($data, $this->getKpiDashboardData($devise, $year));
                $view = 'direction.reports.pdf.kpi-dashboard';
                break;
            case 'services_performance':
                $data = array_merge($data, $this->getServicesPerformanceData($devise, $year, $serviceId));
                $view = 'direction.reports.pdf.services-performance';
                break;
            case 'decisions_history':
                $data = array_merge($data, $this->getDecisionsHistoryData($year));
                $view = 'direction.reports.pdf.decisions-history';
                break;
            case 'monthly_report':
                $data = array_merge($data, $this->getMonthlyReportData($devise, $year, $month));
                $view = 'direction.reports.pdf.monthly-report';
                break;
            case 'annual_report':
                $data = array_merge($data, $this->getAnnualReportData($devise, $year));
                $view = 'direction.reports.pdf.annual-report';
                break;
            default:
                abort(404);
        }
        
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download($reportId . '_' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * 🔥 DONNÉES POUR RAPPORT FINANCIER SYNTHÉTIQUE
     */
    private function getFinancialSummaryData($devise, $year)
    {
        // CA total
        $caTotal = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        })->sum("montant_{$devise}");
        
        // CA année précédente
        $lastYearCA = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year - 1);
        })->sum("montant_{$devise}");
        
        // Marge totale
        $margeTotal = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'MARGE');
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        })->sum("montant_{$devise}");
        
        // Dépenses
        $depenses = FinanceFait::whereHas('activite', function($q) {
            $q->whereIn('type_activite', ['depense', 'achat']);
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        })->sum("montant_{$devise}");
        
        // 🔥 TRÉSORERIE
        $tresorerie = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'TRESO');
        })->sum("montant_{$devise}");
        
        // Investissements
        $investissements = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'INVEST');
        })->whereHas('temps', function($q) use ($year) {
            $q->where('annee', $year);
        })->sum("montant_{$devise}");
        
        // Évolution mensuelle
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($i, $year) {
                $q->where('mois', $i)->where('annee', $year);
            })->sum("montant_{$devise}");
        }
        
        // Croissance
        $croissance = $lastYearCA > 0 ? round(($caTotal - $lastYearCA) / $lastYearCA * 100, 1) : 0;
        
        return [
            'ca_total' => $caTotal,
            'ca_precedent' => $lastYearCA,
            'croissance' => $croissance,
            'marge_total' => $margeTotal,
            'taux_marge' => $caTotal > 0 ? round($margeTotal / $caTotal * 100, 1) : 0,
            'depenses' => $depenses,
            'tresorerie' => $tresorerie,
            'investissements' => $investissements,
            'monthly_data' => $monthlyData,
            'months' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']
        ];
    }
    
    /**
     * 🔥 DONNÉES POUR TABLEAU DE BORD KPI
     */
    private function getKpiDashboardData($devise, $year)
    {
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        foreach ($indicateurs as $indicateur) {
            $indicateur->valeur = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) use ($year) {
                    $q->where('annee', $year);
                })
                ->sum("montant_{$devise}");
            
            // Vérifier alerte
            $indicateur->est_alerte = ($indicateur->seuil_alerte_min && $indicateur->valeur < $indicateur->seuil_alerte_min) ||
                                      ($indicateur->seuil_alerte_max && $indicateur->valeur > $indicateur->seuil_alerte_max);
        }
        
        return [
            'indicateurs' => $indicateurs,
            'total_kpis' => $indicateurs->count(),
            'alertes_count' => $indicateurs->filter(fn($i) => $i->est_alerte)->count()
        ];
    }
    
    /**
     * 🔥 DONNÉES POUR PERFORMANCE PAR SERVICE
     */
    private function getServicesPerformanceData($devise, $year, $serviceId = null)
    {
        $query = Service::where('est_actif', true);
        if ($serviceId) {
            $query->where('id_service', $serviceId);
        }
        $services = $query->get();
        
        foreach ($services as $service) {
            $service->ca = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($year) { $q->where('annee', $year); })
                ->sum("montant_{$devise}");
            
            $service->marge = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
                ->whereHas('temps', function($q) use ($year) { $q->where('annee', $year); })
                ->sum("montant_{$devise}");
            
            $service->performance = $service->ca > 0 ? round($service->marge / $service->ca * 100, 1) : 0;
            $service->employes = $service->utilisateurs()->count();
        }
        
        $totalCA = $services->sum('ca');
        $totalMarge = $services->sum('marge');
        
        return [
            'services' => $services,
            'total_ca' => $totalCA,
            'total_marge' => $totalMarge
        ];
    }
    
    /**
     * 🔥 DONNÉES POUR HISTORIQUE DES DÉCISIONS
     */
    private function getDecisionsHistoryData($year)
    {
        $decisions = Decision::with('createur')
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = [
            'total' => $decisions->count(),
            'validees' => $decisions->where('statut', 'Validée')->count(),
            'rejetees' => $decisions->where('statut', 'Rejetée')->count(),
            'en_attente' => $decisions->where('statut', 'En attente')->count(),
            'taux_validation' => $decisions->count() > 0 ? round($decisions->where('statut', 'Validée')->count() / $decisions->count() * 100, 1) : 0
        ];
        
        return ['decisions' => $decisions, 'stats' => $stats];
    }
    
    /**
     * 🔥 DONNÉES POUR RAPPORT MENSUEL
     */
    private function getMonthlyReportData($devise, $year, $month)
    {
        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        
        $ca = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($year, $month) {
            $q->where('annee', $year)->where('mois', $month);
        })->sum("montant_{$devise}");
        
        // CA mois précédent
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $caPrecedent = FinanceFait::whereHas('indicateur', function($q) {
            $q->where('code_kpi', 'CA');
        })->whereHas('temps', function($q) use ($prevMonth, $prevYear) {
            $q->where('mois', $prevMonth)->where('annee', $prevYear);
        })->sum("montant_{$devise}");
        
        // Performance par service ce mois
        $services = Service::where('est_actif', true)->get();
        foreach ($services as $service) {
            $service->ca = FinanceFait::where('id_service', $service->id_service)
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($year, $month) {
                    $q->where('annee', $year)->where('mois', $month);
                })
                ->sum("montant_{$devise}");
        }
        
        return [
            'month_name' => $monthName,
            'ca' => $ca,
            'ca_precedent' => $caPrecedent,
            'croissance' => $caPrecedent > 0 ? round(($ca - $caPrecedent) / $caPrecedent * 100, 1) : 0,
            'services' => $services->filter(fn($s) => $s->ca > 0),
            'month' => $month,
            'year' => $year
        ];
    }
    
    /**
     * 🔥 DONNÉES POUR RAPPORT ANNUEL
     */
    private function getAnnualReportData($devise, $year)
    {
        $monthlyData = [];
        $prevYearData = [];
        
        for ($i = 1; $i <= 12; $i++) {
            // Année courante
            $monthlyData[] = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($i, $year) {
                $q->where('mois', $i)->where('annee', $year);
            })->sum("montant_{$devise}");
            
            // Année précédente
            $prevYearData[] = FinanceFait::whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })->whereHas('temps', function($q) use ($i, $year) {
                $q->where('mois', $i)->where('annee', $year - 1);
            })->sum("montant_{$devise}");
        }
        
        // Meilleurs mois
        $bestMonths = [];
        foreach ($monthlyData as $index => $value) {
            $bestMonths[] = [
                'month' => Carbon::createFromDate($year, $index + 1, 1)->translatedFormat('F'),
                'value' => $value
            ];
        }
        usort($bestMonths, fn($a, $b) => $b['value'] <=> $a['value']);
        $bestMonths = array_slice($bestMonths, 0, 3);
        
        return [
            'monthly_data' => $monthlyData,
            'prev_year_data' => $prevYearData,
            'total_ca' => array_sum($monthlyData),
            'best_months' => $bestMonths,
            'months' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']
        ];
    }
    
    /**
     * 🔥 TITRE DU RAPPORT
     */
    private function getReportTitle($reportId)
    {
        $titles = [
            'financial_summary' => 'Rapport Financier Synthétique',
            'kpi_dashboard' => 'Tableau de bord des Indicateurs',
            'services_performance' => 'Performance par Service',
            'decisions_history' => 'Historique des Décisions',
            'monthly_report' => 'Rapport Mensuel',
            'annual_report' => 'Rapport Annuel'
        ];
        
        return $titles[$reportId] ?? 'Rapport HYPER PSARO';
    }
}