<?php
// app/Http/Controllers/Direction/UserController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Service;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = Utilisateur::with(['role', 'service']);
        
        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('est_actif', $request->status == 'actif');
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Statistiques
        $stats = [
            'total' => Utilisateur::count(),
            'actifs' => Utilisateur::where('est_actif', true)->count(),
            'inactifs' => Utilisateur::where('est_actif', false)->count(),
            'dg' => Utilisateur::whereHas('role', function($q) { $q->where('code_role', 'DG'); })->count(),
            'conseillers' => Utilisateur::whereHas('role', function($q) { $q->where('code_role', 'CONSEILLER'); })->count(),
            'chefs' => Utilisateur::whereHas('role', function($q) { $q->where('code_role', 'CHEF'); })->count(),
        ];
        
        $roles = Role::all();
        $services = Service::where('est_actif', true)->get();
        
        return view('direction.users.index', compact('users', 'stats', 'roles', 'services'));
    }
    
    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $roles = Role::all();
        $services = Service::where('est_actif', true)->get();
        
        return view('direction.users.create', compact('roles', 'services'));
    }
    
    /**
     * Enregistrer un nouvel utilisateur
     * 🔥 Les nouveaux utilisateurs doivent changer leur mot de passe à la première connexion
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'role_id' => 'required|exists:roles,id_role',
            'service_id' => 'nullable|exists:services,id_service',
            'telephone' => 'nullable|string|max:20',
        ]);
        
        // Générer le matricule automatique
        $matricule = $this->generateMatricule($request->role_id);
        
        $user = Utilisateur::create([
            'matricule' => $matricule,
            'nom' => $request->nom,
            'email' => $request->email,
            'mot_de_passe' => Hash::make('password123'),
            'role_id' => $request->role_id,
            'service_id' => $request->service_id,
            'telephone' => $request->telephone,
            'est_actif' => true,
            'premiere_connexion' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.users.index')
            ->with('success', "Utilisateur {$user->nom} créé avec succès. Mot de passe par défaut : password123");
    }
    
    /**
     * Afficher les détails d'un utilisateur
     * 🔥 CORRIGÉ : Commenté les méthodes qui n'existent pas
     */
    public function show($id)
    {
        $user = Utilisateur::with(['role', 'service'])->findOrFail($id);
        
        // Statistiques de l'utilisateur (simplifiées sans les relations manquantes)
        $stats = [
            'total_operations' => 0, // À implémenter si besoin
            'total_decisions' => 0,  // À implémenter si besoin
            'total_simulations' => 0, // À implémenter si besoin
            'last_login' => $user->last_login_at,
            'last_password_change' => $user->last_password_change,
        ];
        
        return view('direction.users.show', compact('user', 'stats'));
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $user = Utilisateur::findOrFail($id);
        $roles = Role::all();
        $services = Service::where('est_actif', true)->get();
        
        return view('direction.users.edit', compact('user', 'roles', 'services'));
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('utilisateurs', 'email')->ignore($id, 'id_utilisateur')],
            'role_id' => 'required|exists:roles,id_role',
            'service_id' => 'nullable|exists:services,id_service',
            'telephone' => 'nullable|string|max:20',
        ]);
        
        $user->update([
            'nom' => $request->nom,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'service_id' => $request->service_id,
            'telephone' => $request->telephone,
            'updated_at' => now(),
        ]);
        
        return redirect()->route('direction.users.index')
            ->with('success', 'Utilisateur modifié avec succès');
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = Utilisateur::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id_utilisateur == auth()->id()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'], 400);
            }
            return redirect()->route('direction.users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }
        
        $user->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
        }
        
        return redirect()->route('direction.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }
    
    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleStatus($id)
    {
        $user = Utilisateur::findOrFail($id);
        
        if ($user->id_utilisateur == auth()->id()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre statut'], 400);
            }
            return redirect()->route('direction.users.index')->with('error', 'Vous ne pouvez pas modifier votre propre statut');
        }
        
        $user->update(['est_actif' => !$user->est_actif]);
        
        $status = $user->est_actif ? 'activé' : 'désactivé';
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Utilisateur {$status} avec succès"]);
        }
        
        return redirect()->route('direction.users.index')
            ->with('success', "Utilisateur {$status} avec succès");
    }
    
    /**
     * Réinitialiser le mot de passe
     * 🔥 L'utilisateur devra changer son mot de passe à la prochaine connexion
     */
    public function resetPassword($id)
    {
        $user = Utilisateur::findOrFail($id);
        
        $user->update([
            'mot_de_passe' => Hash::make('password123'),
            'premiere_connexion' => true,
            'last_password_change' => null,
        ]);
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Mot de passe réinitialisé à password123. L\'utilisateur devra le changer à la prochaine connexion.']);
        }
        
        return redirect()->route('direction.users.index')
            ->with('success', 'Mot de passe réinitialisé avec succès (password123). L\'utilisateur devra le changer à la prochaine connexion.');
    }
    
    /**
     * Générer un matricule unique
     */
    private function generateMatricule($roleId)
    {
        $role = Role::find($roleId);
        $prefix = $role ? $role->code_role : 'USER';
        
        $lastUser = Utilisateur::where('matricule', 'like', $prefix . '%')
            ->orderBy('matricule', 'desc')
            ->first();
        
        if ($lastUser) {
            $lastNumber = intval(substr($lastUser->matricule, strlen($prefix)));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . date('Y') . $newNumber;
    }
}