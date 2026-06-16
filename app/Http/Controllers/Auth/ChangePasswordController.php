<?php
// app/Http/Controllers/Auth/ChangePasswordController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangeForm()
    {
        return view('auth.change-password');
    }
    
    /**
     * Changer le mot de passe
     */
    public function change(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Vérifier l'ancien mot de passe
        if (!Hash::check($request->current_password, $user->mot_de_passe)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect']);
        }
        
        // Mettre à jour le mot de passe et désactiver la première connexion
        $user->update([
            'mot_de_passe' => Hash::make($request->new_password),
            'premiere_connexion' => false,
            'last_password_change' => now(),
        ]);
        
        // Redirection selon le rôle après changement
        if ($user->isDG()) {
            return redirect()->route('direction.dashboard')->with('success', 'Mot de passe changé avec succès !');
        }
        if ($user->isConseiller()) {
            return redirect()->route('finance.dashboard')->with('success', 'Mot de passe changé avec succès !');
        }
        return redirect()->route('service.dashboard')->with('success', 'Mot de passe changé avec succès !');
    }
}