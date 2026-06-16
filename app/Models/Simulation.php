<?php
// app/Models/Simulation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    protected $table = 'simulations';
    protected $primaryKey = 'id_simulation';
    
    protected $fillable = [
        'nom_simulation', 'description', 'parametres_saisis', 'resultats', 
        'id_utilisateur', 'id_decision', 'est_sauvegardee'
    ];
    
    protected $casts = [
        'parametres_saisis' => 'array',
        'resultats' => 'array',
        'est_sauvegardee' => 'boolean'
    ];
    
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
    
    public function decision()
    {
        return $this->belongsTo(Decision::class, 'id_decision', 'id_decision');
    }
}