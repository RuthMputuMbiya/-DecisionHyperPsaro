<?php
// app/Models/Decision.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decision extends Model
{
    protected $table = 'decisions';
    protected $primaryKey = 'id_decision';
    
    protected $fillable = [
        'titre', 'description', 'parametres', 'resultat_simule', 
        'impact_tresorerie', 'decision_prise', 'cree_par', 'statut', 'date_validation'
    ];
    
    protected $casts = [
        'parametres' => 'array',
        'resultat_simule' => 'decimal:2',
        'impact_tresorerie' => 'decimal:2',
        'date_validation' => 'datetime'
    ];
    
    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par', 'id_utilisateur');
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
}