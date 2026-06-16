<?php
// app/Http/Controllers/Auth/SessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SessionController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ]);
        
        // Chercher l'utilisateur par email OU matricule
        $user = Utilisateur::where('email', $request->identifiant)
                        ->orWhere('matricule', $request->identifiant)
                        ->first();
        
        // Vérification 1 : Utilisateur existe
        if (!$user) {
            return back()->withErrors(['identifiant' => 'Email ou matricule incorrect'])->onlyInput('identifiant');
        }
        
        // Vérification 2 : Compte actif
        if (!$user->est_actif) {
            return back()->withErrors(['identifiant' => 'Votre compte a été désactivé. Contactez la Direction Générale.'])->onlyInput('identifiant');
        }
        
        // Vérification 3 : Mot de passe correct
        if (!Hash::check($request->password, $user->mot_de_passe)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect'])->onlyInput('identifiant');
        }
        
        // 🔥 CONNEXION
        Auth::login($user, $request->has('remember'));
        
        // Mettre à jour la date de dernière connexion
        $user->update(['last_login_at' => now()]);
        
        // 🔥 PREMIÈRE CONNEXION : rediriger vers changement de mot de passe
        if ($user->premiere_connexion) {
            return redirect()->route('password.change')->with('warning', 'Veuillez changer votre mot de passe avant de continuer.');
        }
        
        //  REDIRECTION SELON LE RÔLE
        if ($user->role && $user->role->code_role == 'DG') {
            return redirect()->route('direction.dashboard')->with('success', 'Bienvenue ' . $user->nom . ' !');
        }
        
        if ($user->role && $user->role->code_role == 'CONSEILLER') {
            return redirect()->route('finance.dashboard')->with('success', 'Bienvenue ' . $user->nom . ' !');
        }
        
        if ($user->role && $user->role->code_role == 'CHEF') {
            return redirect()->route('service.dashboard')->with('success', 'Bienvenue ' . $user->nom . ' !');
        }
        
        // Fallback
        return redirect('/dashboard')->with('success', 'Bienvenue ' . $user->nom . ' !');
    }
    
    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}