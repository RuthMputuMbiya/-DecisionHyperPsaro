<?php
// app/Http/Controllers/Direction/IndicateurController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Indicateur;  // ✅ IMPORTANT : Importer le modèle
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IndicateurController extends Controller
{
    /**
     * Afficher la liste des indicateurs
     */
    public function index()
    {
        $indicateurs = Indicateur::paginate(10);
        
        $stats = [
            'total' => Indicateur::count(),
            'actifs' => Indicateur::where('est_actif', true)->count(),
            'inactifs' => Indicateur::where('est_actif', false)->count(),
            'avec_alertes' => Indicateur::whereNotNull('seuil_alerte_min')
                ->orWhereNotNull('seuil_alerte_max')
                ->count(),
        ];
        
        // Ajouter une valeur simulée pour chaque indicateur
        foreach ($indicateurs as $indicateur) {
            if (method_exists($indicateur, 'calculateValue')) {
                $indicateur->valeur_actuelle = $indicateur->calculateValue();
            } else {
                $indicateur->valeur_actuelle = rand(1000, 20000);
            }
        }
        
        return view('direction.indicateurs.index', compact('indicateurs', 'stats'));
    }
    
    /**
     * Afficher le formulaire de création avec les indicateurs pré-définis
     */
    public function create()
    {
        $unites = ['USD', 'CDF', 'EUR', '%', 'Nombre', 'Taux', 'Ratio', 'Jours'];
        $couleurs = [
            '#1a56db' => 'Bleu', '#0e9f6e' => 'Vert', '#e02424' => 'Rouge', 
            '#f5a623' => 'Orange', '#7e3af2' => 'Violet', '#3abff8' => 'Cyan',
            '#10b981' => 'Émeraude', '#f97316' => 'Mandarine', '#8b5cf6' => 'Indigo'
        ];
        
        // Récupérer les indicateurs pré-définis depuis le modèle
        $predefinedIndicators = Indicateur::getPredefinedIndicators();
        
        return view('direction.indicateurs.create', compact('unites', 'couleurs', 'predefinedIndicators'));
    }
    
    /**
     * Récupérer les informations d'un indicateur pré-défini (AJAX)
     */
    public function getPredefined($code)
    {
        $indicators = Indicateur::getPredefinedIndicators();
        $indicator = collect($indicators)->firstWhere('code_kpi', $code);
        
        if ($indicator) {
            return response()->json([
                'success' => true,
                'data' => $indicator
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Indicateur non trouvé'
        ]);
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
            'formule' => 'nullable|string',
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
            'formule' => $request->formule,
            'est_actif' => true,
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
        
        if (method_exists($indicateur, 'calculateValue')) {
            $indicateur->valeur_actuelle = $indicateur->calculateValue();
        } else {
            $indicateur->valeur_actuelle = rand(1000, 20000);
        }
        
        $stats = [
            'total_annee' => $indicateur->valeur_actuelle,
            'total_mois' => method_exists($indicateur, 'calculateValue') ? $indicateur->calculateValue(date('m')) : rand(1000, 20000),
            'meilleur_mois' => null,
        ];
        
        $evolution = [];
        for ($i = 1; $i <= 12; $i++) {
            if (method_exists($indicateur, 'calculateValue')) {
                $evolution[] = $indicateur->calculateValue($i);
            } else {
                $evolution[] = rand(1000, 20000);
            }
        }
        
        $historique = collect([]);
        
        return view('direction.indicateurs.show', compact('indicateur', 'stats', 'evolution', 'historique'));
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
            'code_kpi' => 'required|string|max:20|unique:indicateurs,code_kpi,' . $id . ',id_indicateur',
            'unite' => 'required|string|max:20',
            'description' => 'nullable|string',
            'seuil_alerte_min' => 'nullable|numeric|min:0',
            'seuil_alerte_max' => 'nullable|numeric|min:0',
            'couleur_alerte' => 'nullable|string|max:20',
            'icone' => 'nullable|string|max:50',
            'formule' => 'nullable|string',
        ]);
        
        $indicateur->update([
            'nom_kpi' => $request->nom_kpi,
            'code_kpi' => strtoupper($request->code_kpi),
            'unite' => $request->unite,
            'description' => $request->description,
            'seuil_alerte_min' => $request->seuil_alerte_min,
            'seuil_alerte_max' => $request->seuil_alerte_max,
            'couleur_alerte' => $request->couleur_alerte ?? '#e02424',
            'icone' => $request->icone ?? 'fas fa-chart-line',
            'formule' => $request->formule,
            'est_actif' => $request->has('est_actif'),
        ]);
        
        return redirect()->route('direction.indicateurs.index')
            ->with('success', 'Indicateur mis à jour avec succès');
    }
    
    /**
     * Basculer le statut d'un indicateur
     */
    public function toggleStatus($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $indicateur->est_actif = !$indicateur->est_actif;
        $indicateur->save();
        
        $status = $indicateur->est_actif ? 'activé' : 'désactivé';
        return redirect()->route('direction.indicateurs.index')
            ->with('success', "Indicateur {$status} avec succès");
    }
    
    /**
     * Supprimer un indicateur
     */
    public function destroy($id)
    {
        $indicateur = Indicateur::findOrFail($id);
        $indicateur->delete();
        
        return redirect()->route('direction.indicateurs.index')
            ->with('success', 'Indicateur supprimé avec succès');
    }
}