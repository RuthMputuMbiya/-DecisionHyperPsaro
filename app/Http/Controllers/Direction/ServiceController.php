<?php
// app/Http/Controllers/Direction/ServiceController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Afficher la liste des services
     */
    public function index(Request $request)
    {
        $query = Service::query();
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_service', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('chef_service', 'like', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('est_actif', $request->status == 'actif');
        }
        
        $services = $query->orderBy('nom_service')->paginate(10);
        
        // Statistiques
        $stats = [
            'total' => Service::count(),
            'actifs' => Service::where('est_actif', true)->count(),
            'inactifs' => Service::where('est_actif', false)->count(),
            'budget_total' => Service::sum('budget_annuel'),
            'employes_total' => Utilisateur::whereNotNull('service_id')->count(),
        ];
        
        return view('direction.services.index', compact('services', 'stats'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $roleChef = Role::where('code_role', 'CHEF')->first();
        
        if ($roleChef) {
            $chefsDisponibles = Utilisateur::where('role_id', $roleChef->id_role)
                ->where('est_actif', true)
                ->get();
        } else {
            $chefsDisponibles = collect();
        }
        
        $devises = Devise::all();
        
        return view('direction.services.create', compact('chefsDisponibles', 'devises'));
    }
    
    /**
     * Enregistrer un nouveau service
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_service' => 'required|string|max:100|unique:services,nom_service',
            'description' => 'nullable|string',
            'chef_service' => 'nullable|string|max:255',
            'budget_annuel' => 'nullable|numeric|min:0',
            'budget_devise' => 'nullable|string|in:USD,CDF,EUR',
            'couleur' => 'nullable|string|max:20',
        ]);
        
        $service = Service::create([
            'nom_service' => $request->nom_service,
            'description' => $request->description,
            'chef_service' => $request->chef_service,
            'budget_annuel' => $request->budget_annuel ?? 0,
            'budget_devise' => $request->budget_devise ?? 'USD',
            'couleur' => $request->couleur ?? '#1a56db',
            'est_actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.services.index')
            ->with('success', 'Service créé avec succès');
    }
    
    /**
     * Afficher les détails d'un service
     */
    public function show($id)
    {
        $service = Service::with(['utilisateurs' => function($q) {
            $q->where('est_actif', true);
        }])->findOrFail($id);
        
        $stats = [
            'employes' => $service->utilisateurs->count(),
            'ca_total' => $service->financeFaits()
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->sum('montant_usd'),
            'marge_total' => $service->financeFaits()
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'MARGE'); })
                ->sum('montant_usd'),
            'depenses_total' => $service->financeFaits()
                ->whereHas('activite', function($q) { $q->whereIn('type_activite', ['depense', 'achat']); })
                ->sum('montant_usd'),
            'investissements' => $service->financeFaits()
                ->whereHas('activite', function($q) { $q->where('type_activite', 'investissement'); })
                ->sum('montant_usd'),
        ];
        
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $ca = $service->financeFaits()
                ->whereHas('indicateur', function($q) { $q->where('code_kpi', 'CA'); })
                ->whereHas('temps', function($q) use ($i) {
                    $q->where('mois', $i)->where('annee', date('Y'));
                })
                ->sum('montant_usd');
            $monthlyData[] = $ca;
        }
        
        return view('direction.services.show', compact('service', 'stats', 'monthlyData'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        
        $roleChef = Role::where('code_role', 'CHEF')->first();
        
        if ($roleChef) {
            $chefsDisponibles = Utilisateur::where('role_id', $roleChef->id_role)
                ->where('est_actif', true)
                ->get();
        } else {
            $chefsDisponibles = collect();
        }
        
        $devises = Devise::all();
        
        return view('direction.services.edit', compact('service', 'chefsDisponibles', 'devises'));
    }
    
    /**
     * Mettre à jour un service
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'nom_service' => ['required', 'string', 'max:100', Rule::unique('services', 'nom_service')->ignore($id, 'id_service')],
            'description' => 'nullable|string',
            'chef_service' => 'nullable|string|max:255',
            'budget_annuel' => 'nullable|numeric|min:0',
            'budget_devise' => 'nullable|string|in:USD,CDF,EUR',
            'couleur' => 'nullable|string|max:20',
            'est_actif' => 'nullable|boolean',
        ]);
        
        $service->update([
            'nom_service' => $request->nom_service,
            'description' => $request->description,
            'chef_service' => $request->chef_service,
            'budget_annuel' => $request->budget_annuel ?? 0,
            'budget_devise' => $request->budget_devise ?? $service->budget_devise,
            'couleur' => $request->couleur ?? $service->couleur,
            'est_actif' => $request->has('est_actif'),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.services.index')
            ->with('success', 'Service modifié avec succès');
    }
    
    /**
     * Supprimer un service
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        if ($service->utilisateurs()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Impossible de supprimer ce service car il contient des utilisateurs'
                ], 400);
            }
            return redirect()->route('direction.services.index')
                ->with('error', 'Impossible de supprimer ce service car il contient des utilisateurs');
        }
        
        $service->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Service supprimé avec succès']);
        }
        
        return redirect()->route('direction.services.index')
            ->with('success', 'Service supprimé avec succès');
    }
    
    /**
     * Activer/Désactiver un service
     */
    public function toggleStatus($id)
    {
        $service = Service::findOrFail($id);
        $service->update(['est_actif' => !$service->est_actif]);
        
        $status = $service->est_actif ? 'activé' : 'désactivé';
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Service {$status} avec succès"]);
        }
        
        return redirect()->route('direction.services.index')
            ->with('success', "Service {$status} avec succès");
    }
}