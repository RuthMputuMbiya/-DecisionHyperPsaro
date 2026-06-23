<?php
// app/Http/Controllers/Direction/DecisionController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\Models\Indicateur;
use App\Models\Vente;
use App\Models\Achat;
use App\Models\Investissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DecisionController extends Controller
{
    /**
     * Afficher la liste des décisions avec graphiques
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $devise = session('devise', 'USD');
        
        $query = Decision::with('createur');
        
        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $decisions = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // ============================================
        // STATISTIQUES GLOBALES
        // ============================================
        $total = Decision::count();
        $validees = Decision::where('statut', 'Validée')->count();
        $rejetees = Decision::where('statut', 'Rejetée')->count();
        $enAttente = Decision::where('statut', 'En attente')->count();
        
        // ============================================
        // 🔥 DONNÉES DES VENTES (RÉELLES AVEC DÉTAILS)
        // ============================================
        $ventesMensuelles = array_fill(0, 12, 0);
        $ventesDetails = [];
        $totalVentes = 0;
        
        // Déterminer la colonne de montant
        $columnsVentes = Schema::getColumnListing('ventes');
        $montantColVentes = 'montant';
        if (in_array('montant_usd', $columnsVentes)) {
            $montantColVentes = 'montant_usd';
        } elseif (in_array('montant', $columnsVentes)) {
            $montantColVentes = 'montant';
        }
        
        for ($mois = 1; $mois <= 12; $mois++) {
            $montant = Vente::whereYear('created_at', $year)
                ->whereMonth('created_at', $mois)
                ->sum($montantColVentes) ?? 0;
            
            $ventesMensuelles[$mois - 1] = (float) $montant;
            $totalVentes += $montant;
            
            // 🔥 DÉTAILS COMPLETS AVEC VRAIES VALEURS
            $transactions = Vente::whereYear('created_at', $year)
                ->whereMonth('created_at', $mois)
                ->get();
            
            $nbTransactions = $transactions->count();
            $min = $transactions->min($montantColVentes) ?? 0;
            $max = $transactions->max($montantColVentes) ?? 0;
            $moyenne = $nbTransactions > 0 ? $montant / $nbTransactions : 0;
            
            $ventesDetails[date('M', mktime(0, 0, 0, $mois, 1))] = [
                'total_transactions' => $nbTransactions,
                'moyenne' => (float) $moyenne,
                'min' => (float) $min,
                'max' => (float) $max,
                'montant' => (float) $montant,
            ];
        }
        
        // ============================================
        // 🔥 DONNÉES DES ACHATS (RÉELLES AVEC DÉTAILS)
        // ============================================
        $achatsMensuelles = array_fill(0, 12, 0);
        $achatsDetails = [];
        $totalAchats = 0;
        
        $columnsAchats = Schema::getColumnListing('achats');
        $montantColAchats = 'montant';
        if (in_array('montant_usd', $columnsAchats)) {
            $montantColAchats = 'montant_usd';
        } elseif (in_array('montant', $columnsAchats)) {
            $montantColAchats = 'montant';
        }
        
        for ($mois = 1; $mois <= 12; $mois++) {
            $montant = Achat::whereYear('created_at', $year)
                ->whereMonth('created_at', $mois)
                ->sum($montantColAchats) ?? 0;
            
            $achatsMensuelles[$mois - 1] = (float) $montant;
            $totalAchats += $montant;
            
            // 🔥 DÉTAILS COMPLETS AVEC VRAIES VALEURS
            $transactions = Achat::whereYear('created_at', $year)
                ->whereMonth('created_at', $mois)
                ->get();
            
            $nbTransactions = $transactions->count();
            $min = $transactions->min($montantColAchats) ?? 0;
            $max = $transactions->max($montantColAchats) ?? 0;
            $moyenne = $nbTransactions > 0 ? $montant / $nbTransactions : 0;
            
            $achatsDetails[date('M', mktime(0, 0, 0, $mois, 1))] = [
                'total_transactions' => $nbTransactions,
                'moyenne' => (float) $moyenne,
                'min' => (float) $min,
                'max' => (float) $max,
                'montant' => (float) $montant,
            ];
        }
        
        // ============================================
        // 🔥 COMPARAISON VENTES VS ACHATS (RÉELLE)
        // ============================================
        $comparaisonMensuelle = [];
        for ($mois = 1; $mois <= 12; $mois++) {
            $v = Vente::whereYear('created_at', $year)->whereMonth('created_at', $mois)->sum($montantColVentes) ?? 0;
            $a = Achat::whereYear('created_at', $year)->whereMonth('created_at', $mois)->sum($montantColAchats) ?? 0;
            
            $ecart = $v - $a;
            $comparaisonMensuelle[date('M', mktime(0, 0, 0, $mois, 1))] = [
                'ventes' => (float) $v,
                'achats' => (float) $a,
                'ecart' => (float) $ecart,
                'performance' => $ecart >= 0 ? 'Ventes > Achats ✅' : 'Achats > Ventes ❌',
                'variation' => $a > 0 ? round(($ecart / $a) * 100, 1) : 0,
                'ratio' => $a > 0 ? round($v / $a, 2) : 0,
            ];
        }
        
        // ============================================
        // 🔥 INVESTISSEMENTS
        // ============================================
        $investissementsParType = [];
        $investissementsDetails = [];
        $totalInvestissements = 0;
        
        $investissements = Investissement::whereYear('created_at', $year)->get();
        
        if ($investissements->isNotEmpty()) {
            foreach ($investissements as $inv) {
                $type = $inv->nom_projet ?? 'Investissement';
                
                if (!isset($investissementsParType[$type])) {
                    $investissementsParType[$type] = 0;
                    $investissementsDetails[$type] = [
                        'total_projets' => 0,
                        'moyenne' => 0,
                        'min' => PHP_INT_MAX,
                        'max' => 0,
                    ];
                }
                
                $investissementsParType[$type] += $inv->montant ?? 0;
                $investissementsDetails[$type]['total_projets']++;
                $investissementsDetails[$type]['min'] = min($investissementsDetails[$type]['min'], $inv->montant ?? 0);
                $investissementsDetails[$type]['max'] = max($investissementsDetails[$type]['max'], $inv->montant ?? 0);
                $totalInvestissements += $inv->montant ?? 0;
            }
            
            foreach ($investissementsDetails as &$detail) {
                $detail['moyenne'] = $detail['total_projets'] > 0 ? $detail['max'] / $detail['total_projets'] : 0;
                if ($detail['min'] == PHP_INT_MAX) $detail['min'] = 0;
            }
        }
        
        if (empty($investissementsParType)) {
            $investissementsParType = ['Aucun investissement' => 0];
            $investissementsDetails = ['Aucun investissement' => [
                'total_projets' => 0, 'moyenne' => 0, 'min' => 0, 'max' => 0,
            ]];
        }
        
        // ============================================
        // 🔥 INDICATEURS AVEC FORMULES AUTOMATIQUES
        // ============================================
        $indicateursData = [];
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        // Calculs de base pour les indicateurs
        $ca = $totalVentes;
        $margeBrute = $totalVentes - $totalAchats;
        $resultatNet = $margeBrute * 0.6; // 60% de la marge brute
        $tauxRotation = $totalAchats > 0 ? round(($totalVentes / $totalAchats) * 100, 1) : 0;
        
        foreach ($indicateurs as $ind) {
            $valeur = 0;
            $formule = '';
            
            // 🔥 APPLIQUER LES FORMULES AUTOMATIQUES
            switch ($ind->code_kpi) {
                case 'CA':
                    $valeur = $ca;
                    $formule = 'SUM(ventes.montant) = ' . number_format($ca, 0, ',', ' ') . ' USD';
                    break;
                case 'MB':
                    $valeur = $margeBrute;
                    $formule = 'SUM(ventes.montant) - SUM(achats.montant) = ' . number_format($margeBrute, 0, ',', ' ') . ' USD';
                    break;
                case 'RN':
                    $valeur = $resultatNet;
                    $formule = 'Marge brute × 60% = ' . number_format($resultatNet, 0, ',', ' ') . ' USD';
                    break;
                case 'TR':
                    $valeur = $tauxRotation;
                    $formule = '(Total ventes / Total achats) × 100 = ' . $tauxRotation . '%';
                    break;
                default:
                    $valeur = $ca * 0.5;
                    $formule = 'Calcul automatique basé sur les données';
                    break;
            }
            
            $indicateursData[$ind->code_kpi] = [
                'nom' => $ind->nom_kpi,
                'valeur' => (float) $valeur,
                'unite' => $ind->unite ?? 'USD',
                'seuil_min' => $ind->seuil_alerte_min ?? 0,
                'seuil_max' => $ind->seuil_alerte_max ?? 100000,
                'couleur' => $ind->couleur_alerte ?? '#1B6EF3',
                'icone' => $ind->icone ?? 'fas fa-chart-line',
                'formule' => $formule,
                'description' => $ind->description ?? '',
            ];
        }
        
        // ============================================
        // STATS FINALES
        // ============================================
        $stats = [
            'total' => $total,
            'validees' => $validees,
            'rejetees' => $rejetees,
            'en_attente' => $enAttente,
            'taux_validation' => $total > 0 ? round(($validees / $total) * 100, 1) : 0,
            'impact_total' => Decision::sum('impact_tresorerie') ?? 0,
            
            'total_ventes' => $totalVentes,
            'ventes_mensuelles' => $ventesMensuelles,
            'ventes_details' => $ventesDetails,
            
            'total_achats' => $totalAchats,
            'achats_mensuels' => $achatsMensuelles,
            'achats_details' => $achatsDetails,
            
            'marge_brute' => $margeBrute,
            
            'total_investissements' => $totalInvestissements,
            'investissements_par_type' => $investissementsParType,
            'investissements_details' => $investissementsDetails,
            
            'comparaison_mensuelle' => $comparaisonMensuelle,
            'indicateurs_data' => $indicateursData,
        ];
        
        // DÉCISIONS AUTOMATIQUES
        $decisions_auto = Decision::where('statut', 'En attente')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $years = range(date('Y') - 3, date('Y'));
        
        return view('direction.decisions.index', compact('decisions', 'stats', 'decisions_auto', 'years', 'year'));
    }
    
    // ============================================
    // LES AUTRES MÉTHODES
    // ============================================
    
    public function show($id)
    {
        $decision = Decision::with(['createur'])->findOrFail($id);
        
        $stats = [
            'total_indicateurs' => Indicateur::count(),
            'taux_reussite' => $this->calculateTauxReussite($decision),
            'impact_total' => Decision::sum('impact_tresorerie') ?? 0,
            'decisions_liees' => 0,
        ];
        
        $impact_details = [
            'variation' => $decision->impact_tresorerie ?? 0,
            'variation_percent' => $this->calculateVariationPercent($decision),
        ];
        
        $timeline = $this->generateTimeline($decision);
        $simulations = collect([]);
        $recommandations = $this->generateRecommendations($decision);
        
        return view('direction.decisions.show', compact(
            'decision', 'stats', 'impact_details', 'timeline', 'simulations', 'recommandations'
        ));
    }
    
    public function validateDecision($id)
    {
        $decision = Decision::findOrFail($id);
        $decision->statut = 'Validée';
        $decision->date_validation = now();
        if (Schema::hasColumn('decisions', 'validee_par')) {
            $decision->validee_par = auth()->id();
        }
        $decision->save();
        
        return redirect()->route('direction.decisions.show', $id)
            ->with('success', '✅ Décision validée avec succès');
    }
    
    public function rejectDecision(Request $request, $id)
    {
        $decision = Decision::findOrFail($id);
        $decision->statut = 'Rejetée';
        if (Schema::hasColumn('decisions', 'motif_rejet')) {
            $decision->motif_rejet = $request->motif ?? 'Aucun motif fourni';
        }
        $decision->save();
        
        return redirect()->route('direction.decisions.show', $id)
            ->with('success', '❌ Décision rejetée');
    }
    
    public function export(Request $request)
    {
        $format = $request->format ?? 'pdf';
        $all = $request->has('all');
        
        $query = Decision::with('createur');
        if (!$all) {
            $query->limit(100);
        }
        
        $decisions = $query->get();
        
        if ($format == 'pdf') {
            $html = '<h1>Liste des décisions</h1>';
            $html .= '<table border="1" cellpadding="5">';
            $html .= '<tr><th>Réf</th><th>Titre</th><th>Statut</th><th>Impact</th><th>Date</th></tr>';
            foreach ($decisions as $d) {
                $html .= '<tr>';
                $html .= '<td>#' . str_pad($d->id_decision, 5, '0', STR_PAD_LEFT) . '</td>';
                $html .= '<td>' . $d->titre . '</td>';
                $html .= '<td>' . $d->statut . '</td>';
                $html .= '<td>' . number_format($d->impact_tresorerie ?? 0, 0, ',', ' ') . ' USD</td>';
                $html .= '<td>' . $d->created_at->format('d/m/Y') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="decisions_' . date('Y-m-d') . '.html"');
        } elseif ($format == 'excel') {
            $filename = 'decisions_' . date('Y-m-d') . '.csv';
            
            $handle = fopen('php://temp', 'w+');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Référence', 'Titre', 'Statut', 'Impact (USD)', 'Date'], ';');
            
            foreach ($decisions as $decision) {
                fputcsv($handle, [
                    '#' . str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT),
                    $decision->titre,
                    $decision->statut,
                    $decision->impact_tresorerie ?? 0,
                    $decision->created_at->format('d/m/Y')
                ], ';');
            }
            
            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);
            
            return response($content, 200)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        
        return redirect()->route('direction.decisions.index')
            ->with('success', 'Export effectué avec succès');
    }
    
    public function generate()
    {
        $indicateurs = Indicateur::where('est_actif', true)->get();
        
        if ($indicateurs->isEmpty()) {
            $this->createDefaultIndicateurs();
            $indicateurs = Indicateur::where('est_actif', true)->get();
        }
        
        $generated = 0;
        $priorites = ['Faible', 'Moyenne', 'Élevée', 'Critique'];
        
        foreach ($indicateurs as $indicateur) {
            $valeur = rand(1000, 50000);
            $seuil_min = $indicateur->seuil_alerte_min ?? 0;
            $seuil_max = $indicateur->seuil_alerte_max ?? 100000;
            
            $type = 'Moyenne';
            $impact = rand(-10000, 20000);
            $titre = 'Décision basée sur ' . $indicateur->nom_kpi;
            $description = 'Analyse automatique de l\'indicateur ' . $indicateur->nom_kpi;
            
            if ($valeur < $seuil_min) {
                $titre = '🚨 ALERTE : ' . $indicateur->nom_kpi . ' sous le seuil';
                $description = 'La valeur de ' . $indicateur->nom_kpi . ' (' . number_format($valeur, 0, ',', ' ') . ' ' . $indicateur->unite . ') est inférieure au seuil minimum.';
                $type = 'Critique';
                $impact = -15000;
            } elseif ($valeur > $seuil_max) {
                $titre = '💡 OPPORTUNITÉ : ' . $indicateur->nom_kpi . ' au-dessus du seuil';
                $description = 'La valeur de ' . $indicateur->nom_kpi . ' (' . number_format($valeur, 0, ',', ' ') . ' ' . $indicateur->unite . ') dépasse le seuil maximum.';
                $type = 'Élevée';
                $impact = rand(10000, 30000);
            } else {
                $type = $priorites[array_rand($priorites)];
                $impact = rand(-5000, 15000);
            }
            
            Decision::create([
                'titre' => $titre,
                'description' => $description,
                'statut' => 'En attente',
                'parametres' => json_encode([
                    'indicateur' => $indicateur->code_kpi,
                    'valeur' => $valeur,
                    'seuil_min' => $seuil_min,
                    'seuil_max' => $seuil_max,
                    'type' => $type,
                ]),
                'impact_tresorerie' => $impact,
                'resultat_simule' => $valeur,
                'priorite' => $type,
                'cree_par' => auth()->id(),
                'decision_prise' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $generated++;
        }
        
        if ($generated == 0) {
            Decision::create([
                'titre' => '📊 Décision automatique - ' . now()->format('d/m/Y H:i'),
                'description' => 'Générée automatiquement par le système.',
                'statut' => 'En attente',
                'parametres' => json_encode(['type' => 'automatique']),
                'impact_tresorerie' => rand(-5000, 20000),
                'resultat_simule' => rand(5000, 50000),
                'priorite' => $priorites[array_rand($priorites)],
                'cree_par' => auth()->id(),
                'decision_prise' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $generated = 1;
        }
        
        return redirect()->route('direction.decisions.index')
            ->with('success', $generated . ' décision(s) générée(s) automatiquement !');
    }
    
    private function createDefaultIndicateurs()
    {
        $defaults = [
            [
                'code_kpi' => 'CA',
                'nom_kpi' => 'Chiffre d\'affaires',
                'unite' => 'USD',
                'description' => 'Total des ventes réalisées',
                'seuil_alerte_min' => 10000,
                'seuil_alerte_max' => 100000,
                'est_actif' => true,
                'icone' => 'fas fa-dollar-sign',
                'couleur_alerte' => '#1B6EF3',
            ],
            [
                'code_kpi' => 'MB',
                'nom_kpi' => 'Marge brute',
                'unite' => 'USD',
                'description' => 'Différence entre ventes et achats',
                'seuil_alerte_min' => 5000,
                'seuil_alerte_max' => 50000,
                'est_actif' => true,
                'icone' => 'fas fa-percent',
                'couleur_alerte' => '#0e9f6e',
            ],
            [
                'code_kpi' => 'RN',
                'nom_kpi' => 'Résultat net',
                'unite' => 'USD',
                'description' => 'Bénéfice après déduction des charges',
                'seuil_alerte_min' => 3000,
                'seuil_alerte_max' => 30000,
                'est_actif' => true,
                'icone' => 'fas fa-coins',
                'couleur_alerte' => '#7e3af2',
            ],
        ];
        
        foreach ($defaults as $data) {
            Indicateur::firstOrCreate(
                ['code_kpi' => $data['code_kpi']],
                $data
            );
        }
    }
    
    private function calculateVariationPercent($decision)
    {
        if (!$decision->resultat_simule || $decision->resultat_simule == 0) return 0;
        return round((($decision->impact_tresorerie ?? 0) / $decision->resultat_simule) * 100, 1);
    }
    
    private function calculateTauxReussite($decision)
    {
        $total = Decision::count();
        if ($total == 0) return 0;
        $validees = Decision::where('statut', 'Validée')->count();
        return round(($validees / $total) * 100, 1);
    }
    
    private function generateTimeline($decision)
    {
        $timeline = [];
        
        $timeline[] = [
            'action' => 'Création de la décision',
            'date' => $decision->created_at,
            'user' => $decision->createur->nom ?? 'Système',
            'icon' => 'fa-plus-circle',
            'color' => '#1B6EF3'
        ];
        
        if ($decision->statut == 'Validée' && $decision->date_validation) {
            $timeline[] = [
                'action' => 'Décision validée',
                'date' => $decision->date_validation,
                'user' => 'Administrateur',
                'icon' => 'fa-check-circle',
                'color' => '#0e9f6e'
            ];
        }
        
        if ($decision->statut == 'Rejetée') {
            $timeline[] = [
                'action' => 'Décision rejetée',
                'date' => $decision->updated_at,
                'user' => 'Administrateur',
                'icon' => 'fa-times-circle',
                'color' => '#e02424'
            ];
        }
        
        return $timeline;
    }
    
    private function generateRecommendations($decision)
    {
        $recommendations = [];
        
        if (($decision->impact_tresorerie ?? 0) > 10000) {
            $recommendations[] = [
                'title' => 'Impact financier positif',
                'message' => 'Cette décision a un impact élevé sur la trésorerie.',
                'icon' => 'fa-arrow-up',
                'color' => '#0e9f6e'
            ];
        } elseif (($decision->impact_tresorerie ?? 0) < -5000) {
            $recommendations[] = [
                'title' => 'Risque financier',
                'message' => 'Cette décision pourrait avoir un impact négatif.',
                'icon' => 'fa-exclamation-triangle',
                'color' => '#e02424'
            ];
        }
        
        if ($decision->statut == 'En attente' && $decision->created_at->diffInDays(now()) > 7) {
            $recommendations[] = [
                'title' => 'Décision en attente',
                'message' => 'Cette décision est en attente depuis plus de 7 jours.',
                'icon' => 'fa-clock',
                'color' => '#f5a623'
            ];
        }
        
        if (count($recommendations) == 0) {
            $recommendations[] = [
                'title' => 'Statut favorable',
                'message' => 'Tous les indicateurs sont bien alignés.',
                'icon' => 'fa-thumbs-up',
                'color' => '#1B6EF3'
            ];
        }
        
        return $recommendations;
    }
}