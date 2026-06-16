<?php
// app/Http/Middleware/CheckFirstLogin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckFirstLogin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user && $user->premiere_connexion) {
            if ($request->route()->getName() != 'password.change' && 
                $request->route()->getName() != 'password.change.submit') {
                return redirect()->route('password.change');
            }
        }
        
        return $next($request);
    }
}