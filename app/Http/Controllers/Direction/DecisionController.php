<?php
// app/Http/Controllers/Direction/DecisionController.php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    public function index(Request $request)
    {
        $query = Decision::with('createur');
        
        if ($request->filled('status')) {
            $query->where('statut', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $decisions = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $total = Decision::count();
        $validees = Decision::where('statut', 'Validée')->count();
        
        // 🔥 AJOUTER taux_validation
        $stats = [
            'total' => $total,
            'validees' => $validees,
            'rejetees' => Decision::where('statut', 'Rejetée')->count(),
            'en_attente' => Decision::where('statut', 'En attente')->count(),
            'taux_validation' => $total > 0 ? round(($validees / $total) * 100, 1) : 0,
            'impact_total' => Decision::sum('impact_tresorerie'),
        ];
        
        return view('direction.decisions.index', compact('decisions', 'stats'));
    }
    
    public function show($id)
    {
        $decision = Decision::with(['createur', 'simulations'])->findOrFail($id);
        return view('direction.decisions.show', compact('decision'));
    }
}