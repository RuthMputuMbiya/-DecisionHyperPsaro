<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ]);
        
        // Chercher par matricule OU email
        $user = Utilisateur::where('matricule', $request->identifiant)
                        ->orWhere('email', $request->identifiant)
                        ->first();
        
        // DEBUG : Afficher l'utilisateur trouvé (à supprimer après test)
        \Log::info('Tentative de connexion', [
            'identifiant' => $request->identifiant,
            'user_found' => $user ? $user->email : 'Aucun'
        ]);
        
        if (!$user) {
            return back()->withErrors(['identifiant' => 'Matricule ou email incorrect'])->onlyInput('identifiant');
        }
        
        if (!$user->est_actif) {
            return back()->withErrors(['identifiant' => 'Votre compte a été désactivé.'])->onlyInput('identifiant');
        }
        
        if (!Hash::check($request->password, $user->mot_de_passe)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect'])->onlyInput('identifiant');
        }
        
        // Connexion
        Auth::login($user, $request->remember);
        $user->update(['last_login_at' => now()]);
        
        // 🔥 PREMIÈRE CONNEXION
        if ($user->premiere_connexion) {
            return redirect()->route('password.change');
        }
        
        // 🔥 REDIRECTION SELON LE RÔLE
        if ($user->role && $user->role->code_role == 'DG') {
            return redirect()->route('direction.dashboard');
        }
        
        if ($user->role && $user->role->code_role == 'CONSEILLER') {
            return redirect()->route('finance.dashboard');
        }
        
        if ($user->role && $user->role->code_role == 'CHEF') {
            return redirect()->route('service.dashboard');
        }
        
        // Fallback
        return redirect('/dashboard');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}