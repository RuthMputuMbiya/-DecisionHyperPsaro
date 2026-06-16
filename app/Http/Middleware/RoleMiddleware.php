<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        if (!$user->role) {
            abort(403, 'Aucun rôle assigné à cet utilisateur.');
        }
        
        if (in_array($user->role->code_role, $roles)) {
            return $next($request);
        }
        
        abort(403, 'Accès non autorisé. Vous n\'avez pas les droits nécessaires.');
    }
}