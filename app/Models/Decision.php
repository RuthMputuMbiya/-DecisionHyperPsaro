<?php
// app/Models/Decision.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    protected $table = 'decisions';
    protected $primaryKey = 'id_decision';
    
    protected $fillable = [
        'titre',
        'description',
        'statut',
        'parametres',
        'impact_tresorerie',
        'resultat_simule',
        'priorite',
        'cree_par',
        'validee_par',
        'date_validation',
        'motif_rejet',
        'decision_prise',
        'is_auto',
        'decision_id',
    ];
    
    protected $casts = [
        'parametres' => 'array',
        'impact_tresorerie' => 'decimal:2',
        'resultat_simule' => 'decimal:2',
        'date_validation' => 'datetime',
        'is_auto' => 'boolean',
    ];
    
    protected $attributes = [
        'statut' => 'En attente',
        'priorite' => 'Moyenne',
        'parametres' => null,
        'is_auto' => false,
        'decision_prise' => null,
    ];
    
    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par', 'id_utilisateur');
    }
    
    public function valideur()
    {
        return $this->belongsTo(Utilisateur::class, 'validee_par', 'id_utilisateur');
    }
    
    public function simulations()
    {
        return $this->hasMany(Simulation::class, 'id_decision', 'id_decision');
    }
    
    public function estValidee()
    {
        return $this->statut === 'Validée';
    }
    
    public function estRejetee()
    {
        return $this->statut === 'Rejetée';
    }
    
    public function estEnAttente()
    {
        return $this->statut === 'En attente';
    }
}