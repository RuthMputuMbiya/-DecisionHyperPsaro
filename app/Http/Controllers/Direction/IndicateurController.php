<?php
// app/Http/Controllers/Direction/IndicateurController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Indicateur;
use App\Models\FinanceFait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IndicateurController extends Controller
{
    /**
     * Afficher la liste des indicateurs
     */
    public function index(Request $request)
    {
        $query = Indicateur::query();
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_kpi', 'like', "%{$search}%")
                  ->orWhere('code_kpi', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('est_actif', $request->status == 'actif');
        }
        
        $indicateurs = $query->orderBy('code_kpi')->paginate(10);
        
        // Statistiques
        $stats = [
            'total' => Indicateur::count(),
            'actifs' => Indicateur::where('est_actif', true)->count(),
            'inactifs' => Indicateur::where('est_actif', false)->count(),
            'avec_alertes' => Indicateur::whereNotNull('seuil_alerte_min')->orWhereNotNull('seuil_alerte_max')->count(),
        ];
        
        // Valeurs actuelles des indicateurs
        foreach ($indicateurs as $indicateur) {
            $indicateur->valeur_actuelle = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) {
                    $q->where('annee', date('Y'));
                })
                ->sum('montant_usd');
        }
        
        return view('direction.indicateurs.index', compact('indicateurs', 'stats'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $unites = ['USD', 'CDF', 'EUR', '%', 'Nombre', 'Taux', 'Ratio', 'Jours'];
        $couleurs = [
            '#1a56db' => 'Bleu', '#0e9f6e' => 'Vert', '#e02424' => 'Rouge', 
            '#f5a623' => 'Orange', '#7e3af2' => 'Violet', '#3abff8' => 'Cyan',
            '#10b981' => 'Émeraude', '#f97316' => 'Mandarine', '#8b5cf6' => 'Indigo'
        ];
        
        return view('direction.indicateurs.create', compact('unites', 'couleurs'));
    }
    
    /**
     * Enregistrer un nouvel indicateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_kpi' => 'required|string|max:100',
            'code_kpi' => 'required|string|max:20|unique:indicateurs,code_kpi',
            'unite' => 'required|string|max:20',
            'description' => 'nullable|string',
            'seuil_alerte_min' => 'nullable|numeric|min:0',
            'seuil_alerte_max' => 'nullable|numeric|min:0',
            'couleur_alerte' => 'nullable|string|max:20',
            'icone' => 'nullable|string|max:50',
        ]);
        
        $indicateur = Indicateur::create([
            'nom_kpi' => $request->nom_kpi,
            'code_kpi' => strtoupper($request->code_kpi),
            'unite' => $request->unite,
            'description' => $request->description,
            'seuil_alerte_min' => $request->seuil_alerte_min,
            'seuil_alerte_max' => $request->seuil_alerte_max,
            'couleur_alerte' => $request->couleur_alerte ?? '#e02424',
            'icone' => $request->icone ?? 'fas fa-chart-line',
            'est_actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.indicateurs.index')
            ->with('success', 'Indicateur créé avec succès');
    }
    
    /**
     * Afficher les détails d'un indicateur
     */
    public function show($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        
        // Historique des valeurs
        $historique = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
            ->with('temps')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Évolution mensuelle
        $evolution = [];
        for ($i = 1; $i <= 12; $i++) {
            $valeur = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) use ($i) {
                    $q->where('mois', $i)->where('annee', date('Y'));
                })
                ->sum('montant_usd');
            $evolution[] = $valeur;
        }
        
        // Statistiques
        $stats = [
            'total_annee' => FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) { $q->where('annee', date('Y')); })
                ->sum('montant_usd'),
            'total_mois' => FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->whereHas('temps', function($q) { $q->where('mois', date('m'))->where('annee', date('Y')); })
                ->sum('montant_usd'),
            'meilleur_mois' => FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
                ->selectRaw('MONTH(date) as mois, SUM(montant_usd) as total')
                ->groupBy('mois')
                ->orderBy('total', 'desc')
                ->first(),
        ];
        
        return view('direction.indicateurs.show', compact('indicateur', 'historique', 'evolution', 'stats'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $unites = ['USD', 'CDF', 'EUR', '%', 'Nombre', 'Taux', 'Ratio', 'Jours'];
        $couleurs = [
            '#1a56db' => 'Bleu', '#0e9f6e' => 'Vert', '#e02424' => 'Rouge', 
            '#f5a623' => 'Orange', '#7e3af2' => 'Violet', '#3abff8' => 'Cyan',
            '#10b981' => 'Émeraude', '#f97316' => 'Mandarine', '#8b5cf6' => 'Indigo'
        ];
        
        return view('direction.indicateurs.edit', compact('indicateur', 'unites', 'couleurs'));
    }
    
    /**
     * Mettre à jour un indicateur
     */
    public function update(Request $request, $id)
    {
        $indicateur = Indicateur::findOrFail($id);
        
        $request->validate([
            'nom_kpi' => 'required|string|max:100',
            'code_kpi' => ['required', 'string', 'max:20', Rule::unique('indicateurs', 'code_kpi')->ignore($id, 'id_indicateur')],
            'unite' => 'required|string|max:20',
            'description' => 'nullable|string',
            'seuil_alerte_min' => 'nullable|numeric|min:0',
            'seuil_alerte_max' => 'nullable|numeric|min:0',
            'couleur_alerte' => 'nullable|string|max:20',
            'icone' => 'nullable|string|max:50',
            'est_actif' => 'nullable|boolean',
        ]);
        
        $indicateur->update([
            'nom_kpi' => $request->nom_kpi,
            'code_kpi' => strtoupper($request->code_kpi),
            'unite' => $request->unite,
            'description' => $request->description,
            'seuil_alerte_min' => $request->seuil_alerte_min,
            'seuil_alerte_max' => $request->seuil_alerte_max,
            'couleur_alerte' => $request->couleur_alerte ?? $indicateur->couleur_alerte,
            'icone' => $request->icone ?? $indicateur->icone,
            'est_actif' => $request->has('est_actif'),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.indicateurs.index')
            ->with('success', 'Indicateur modifié avec succès');
    }
    
    /**
     * Supprimer un indicateur
     */
    public function destroy($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        
        // Vérifier si l'indicateur a des données
        if (FinanceFait::where('id_indicateur', $indicateur->id_indicateur)->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Impossible de supprimer cet indicateur car il a des données associées'
                ], 400);
            }
            return redirect()->route('direction.indicateurs.index')
                ->with('error', 'Impossible de supprimer cet indicateur car il a des données associées');
        }
        
        $indicateur->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Indicateur supprimé avec succès']);
        }
        
        return redirect()->route('direction.indicateurs.index')
            ->with('success', 'Indicateur supprimé avec succès');
    }
    
    /**
     * 🔥 Activer/Désactiver un indicateur (méthode POST)
     */
    public function toggleStatus($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $indicateur->update(['est_actif' => !$indicateur->est_actif]);
        
        $status = $indicateur->est_actif ? 'activé' : 'désactivé';
        
        // Redirection avec message flash
        return redirect()->route('direction.indicateurs.index')
            ->with('success', "Indicateur {$status} avec succès");
    }
    
    /**
     * Vérifier l'état d'alerte d'un indicateur
     */
    public function checkAlert($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $valeur_actuelle = FinanceFait::where('id_indicateur', $indicateur->id_indicateur)
            ->whereHas('temps', function($q) {
                $q->where('annee', date('Y'));
            })
            ->sum('montant_usd');
        
        $en_alerte = false;
        $message = '';
        
        if ($indicateur->seuil_alerte_min && $valeur_actuelle < $indicateur->seuil_alerte_min) {
            $en_alerte = true;
            $message = "Valeur inférieure au seuil minimum ({$indicateur->seuil_alerte_min} {$indicateur->unite})";
        } elseif ($indicateur->seuil_alerte_max && $valeur_actuelle > $indicateur->seuil_alerte_max) {
            $en_alerte = true;
            $message = "Valeur supérieure au seuil maximum ({$indicateur->seuil_alerte_max} {$indicateur->unite})";
        }
        
        return response()->json([
            'en_alerte' => $en_alerte,
            'message' => $message,
            'valeur_actuelle' => $valeur_actuelle,
            'seuil_min' => $indicateur->seuil_alerte_min,
            'seuil_max' => $indicateur->seuil_alerte_max
        ]);
    }
}