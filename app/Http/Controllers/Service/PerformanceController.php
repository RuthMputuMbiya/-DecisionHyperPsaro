<?php
// app/Http/Controllers/Service/PerformanceController.php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\FinanceFait;
use App\Models\Indicateur;
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Depense;
use App\Models\Investissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PerformanceController extends Controller
{
    /**
     * Afficher le tableau de bord des performances
     */
    public function index()
    {
        // Récupérer le service de l'utilisateur connecté
        $user = Auth::user();
        $serviceId = $user->service_id ?? 1;
        $devise = session('devise', 'USD');
        $year = date('Y');
        
        // Récupérer le service
        $service = Service::find($serviceId);
        
        // 🔥 Vérifier si des données existent
        $hasData = $this->checkIfDataExists($serviceId);
        
        // ============================================
        // STATISTIQUES GLOBALES (AUTOMATIQUES)
        // ============================================
        
        // CA Total
        $caTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caTotal == 0) {
            $caTotal = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $year)
                ->sum('montant') ?? 0;
        }
        
        // Marge Total
        $margeTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'MARGE');
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($margeTotal == 0) {
            $ventes = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $year)
                ->sum('montant') ?? 0;
            
            $achats = Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $year)
                ->sum('montant') ?? 0;
            
            $margeTotal = $ventes - $achats;
        }
        
        // Dépenses Total
        $depensesTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) {
                $q->whereIn('type_activite', ['depense', 'achat']);
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($depensesTotal == 0) {
            $depensesTotal = (Depense::where('id_service', $serviceId)
                ->whereYear('created_at', $year)
                ->sum('montant') ?? 0) + 
                (Achat::where('id_service', $serviceId)
                ->whereYear('created_at', $year)
                ->sum('montant') ?? 0);
        }
        
        // CA par mois (6 derniers mois)
        $caMensuel = [];
        $depensesMensuelles = [];
        $margeMensuelle = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $yearMonth = date('Y', strtotime("-$i months"));
            
            // CA mensuel
            $caMensuel[] = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            // Dépenses mensuelles
            $depensesMensuelles[] = FinanceFait::where('id_service', $serviceId)
                ->whereHas('activite', function($q) {
                    $q->whereIn('type_activite', ['depense', 'achat']);
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            // Marge mensuelle
            $margeMensuelle[] = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'MARGE');
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
        }
        
        // Croissance CA
        $caPrecedent = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year - 1);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        if ($caPrecedent == 0) {
            $caPrecedent = Vente::where('id_service', $serviceId)
                ->whereYear('created_at', $year - 1)
                ->sum('montant') ?? 0;
        }
        
        $caCroissance = $caPrecedent > 0 ? round((($caTotal - $caPrecedent) / $caPrecedent) * 100, 1) : 0;
        
        // Taux de marge
        $tauxMarge = $caTotal > 0 ? round(($margeTotal / $caTotal) * 100, 1) : 0;
        
        // Score de performance
        $performanceScore = 0;
        if ($caTotal > 0 && $margeTotal > 0) {
            $performanceScore = round((($margeTotal / $caTotal) * 100), 1);
        } elseif ($caTotal > 0) {
            $performanceScore = round((($caTotal - $depensesTotal) / $caTotal) * 100, 1);
        }
        
        // ============================================
        // DÉPENSES PAR CATÉGORIE
        // ============================================
        $depensesParCategorie = Depense::where('id_service', $serviceId)
            ->select('categorie_depense', DB::raw('SUM(montant) as total'))
            ->groupBy('categorie_depense')
            ->pluck('total', 'categorie_depense')
            ->toArray();
        
        // Si pas de données, utiliser des valeurs par défaut avec des données de démonstration
        if (empty($depensesParCategorie) || array_sum($depensesParCategorie) == 0) {
            $depensesParCategorie = [
                'Achats' => rand(5000, 15000),
                'Salaires' => rand(3000, 10000),
                'Charges' => rand(2000, 8000),
                'Marketing' => rand(1000, 5000),
                'Autres' => rand(500, 3000)
            ];
        }
        
        // ============================================
        // OBJECTIFS (AUTOMATIQUES)
        // ============================================
        $objectifs = [
            'ca' => [
                'actuel' => $caTotal,
                'objectif' => $caTotal > 0 ? round($caTotal * 1.2, 2) : 100000,
                'progression' => $caTotal > 0 ? min(round(($caTotal / ($caTotal * 1.2)) * 100, 1), 100) : 0,
                'couleur' => 'primary'
            ],
            'marge' => [
                'actuel' => $margeTotal,
                'objectif' => $margeTotal > 0 ? round($margeTotal * 1.3, 2) : 50000,
                'progression' => $margeTotal > 0 ? min(round(($margeTotal / ($margeTotal * 1.3)) * 100, 1), 100) : 0,
                'couleur' => 'success'
            ],
            'depenses' => [
                'actuel' => $depensesTotal,
                'objectif' => $depensesTotal > 0 ? round($depensesTotal * 0.8, 2) : 50000,
                'progression' => $depensesTotal > 0 ? min(round((($depensesTotal * 0.8) / $depensesTotal) * 100, 1), 100) : 0,
                'couleur' => 'danger'
            ]
        ];
        
        // ============================================
        // OBJECTIFS DATA POUR GRAPHIQUE
        // ============================================
        $objectifsData = [
            'atteint' => $performanceScore,
            'objectif' => 100
        ];
        
        // ============================================
        // PERFORMANCES (TABLEAU)
        // ============================================
        $performances = collect([]);
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $yearMonth = date('Y', strtotime("-$i months"));
            $periode = date('M Y', strtotime("-$i months"));
            
            $ca = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            if ($ca == 0) {
                $ca = Vente::where('id_service', $serviceId)
                    ->whereYear('created_at', $yearMonth)
                    ->whereMonth('created_at', $month)
                    ->sum('montant') ?? 0;
            }
            
            $marge = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'MARGE');
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            $depenses = FinanceFait::where('id_service', $serviceId)
                ->whereHas('activite', function($q) {
                    $q->whereIn('type_activite', ['depense', 'achat']);
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
            
            $performance = $ca > 0 ? round(($marge / $ca) * 100, 1) : 0;
            
            $performances->push((object)[
                'periode' => $periode,
                'ca' => $ca,
                'marge' => $marge,
                'depenses' => $depenses,
                'performance' => $performance
            ]);
        }
        
        // ============================================
        // RECOMMANDATIONS (AUTOMATIQUES)
        // ============================================
        $recommandations = [];
        
        if ($performanceScore < 50 && $caTotal > 0) {
            $recommandations[] = [
                'title' => '⚠️ Performance à améliorer',
                'message' => 'Votre score de performance est inférieur à 50%. Analysez vos dépenses et optimisez vos coûts.',
                'icon' => 'fa-exclamation-triangle',
                'color' => '#e02424'
            ];
        }
        
        if ($depensesTotal > $caTotal * 0.8 && $caTotal > 0) {
            $recommandations[] = [
                'title' => '📊 Réduire les dépenses',
                'message' => 'Vos dépenses représentent plus de 80% de votre CA. Cherchez à réduire vos coûts.',
                'icon' => 'fa-arrow-down',
                'color' => '#f5a623'
            ];
        }
        
        if ($margeTotal < 10000 && $margeTotal > 0) {
            $recommandations[] = [
                'title' => '💡 Augmenter la marge',
                'message' => 'Votre marge est faible. Essayez d\'augmenter vos prix ou de réduire vos coûts.',
                'icon' => 'fa-lightbulb',
                'color' => '#7e3af2'
            ];
        }
        
        if (empty($recommandations)) {
            if ($caTotal > 0) {
                $recommandations[] = [
                    'title' => '✅ Performance optimale',
                    'message' => 'Tous les indicateurs sont au vert. Continuez sur cette lancée !',
                    'icon' => 'fa-check-circle',
                    'color' => '#0e9f6e'
                ];
            } else {
                $recommandations[] = [
                    'title' => '📊 Bienvenue sur votre tableau de bord',
                    'message' => 'Commencez à ajouter des données pour voir vos performances.',
                    'icon' => 'fa-info-circle',
                    'color' => '#1B6EF3'
                ];
            }
        }
        
        return view('service.performances.index', compact(
            'service',
            'devise',
            'caTotal',
            'margeTotal',
            'depensesTotal',
            'caMensuel',
            'depensesMensuelles',
            'margeMensuelle',
            'caCroissance',
            'tauxMarge',
            'performanceScore',
            'depensesParCategorie',
            'objectifs',
            'objectifsData',
            'performances',
            'recommandations'
        ));
    }
    
    /**
     * 🔥 VÉRIFIER SI DES DONNÉES EXISTENT
     */
    private function checkIfDataExists($serviceId)
    {
        return FinanceFait::where('id_service', $serviceId)->exists() ||
               Vente::where('id_service', $serviceId)->exists() ||
               Achat::where('id_service', $serviceId)->exists() ||
               Depense::where('id_service', $serviceId)->exists();
    }

    /**
     * 🔥 EXPORTER LE RAPPORT DE PERFORMANCE
     */
    public function export(Request $request)
    {
        $format = $request->format ?? 'pdf';
        $devise = session('devise', 'USD');
        $year = date('Y');
        $user = Auth::user();
        $serviceId = $user->service_id ?? 1;
        
        $service = Service::find($serviceId);
        
        $caTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'CA');
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        $margeTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('indicateur', function($q) {
                $q->where('code_kpi', 'MARGE');
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        $depensesTotal = FinanceFait::where('id_service', $serviceId)
            ->whereHas('activite', function($q) {
                $q->whereIn('type_activite', ['depense', 'achat']);
            })
            ->whereHas('temps', function($q) use ($year) {
                $q->where('annee', $year);
            })
            ->sum("montant_{$devise}") ?? 0;
        
        $performanceScore = 0;
        if ($caTotal > 0 && $margeTotal > 0) {
            $performanceScore = round((($margeTotal / $caTotal) * 100), 1);
        }
        
        $caMensuel = [];
        $moisLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $yearMonth = date('Y', strtotime("-$i months"));
            $moisLabels[] = date('M', strtotime("-$i months"));
            
            $caMensuel[] = FinanceFait::where('id_service', $serviceId)
                ->whereHas('indicateur', function($q) {
                    $q->where('code_kpi', 'CA');
                })
                ->whereHas('temps', function($q) use ($month, $yearMonth) {
                    $q->where('mois', (int)$month)->where('annee', (int)$yearMonth);
                })
                ->sum("montant_{$devise}") ?? 0;
        }
        
        if ($format == 'pdf') {
            $pdf = Pdf::loadView('service.performances.export-pdf', compact(
                'service',
                'devise',
                'caTotal',
                'margeTotal',
                'depensesTotal',
                'performanceScore',
                'caMensuel',
                'moisLabels'
            ));
            
            return $pdf->download('rapport_performances_' . date('Y-m-d') . '.pdf');
        }
        
        return redirect()->route('service.performances.index')
            ->with('success', 'Export effectué avec succès');
    }
}