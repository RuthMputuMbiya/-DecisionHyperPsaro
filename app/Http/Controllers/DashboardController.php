<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) return redirect()->route('login');
        
        if ($user->role && $user->role->code_role == 'DG') {
            return redirect()->route('direction.dashboard');
        }
        if ($user->role && $user->role->code_role == 'CONSEILLER') {
            return redirect()->route('finance.dashboard');
        }
        if ($user->role && $user->role->code_role == 'CHEF') {
            return redirect()->route('service.dashboard');
        }
        
        return view('dashboard.index');
    }
    
    public function changeCurrency(Request $request)
    {
        $request->validate(['devise' => 'required|in:CDF,USD,EUR']);
        session(['devise' => $request->devise]);
        return back()->with('success', 'Devise changée');
    }
}