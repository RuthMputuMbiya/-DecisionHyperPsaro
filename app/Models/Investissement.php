<?php
// app/Models/Investissement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investissement extends Model
{
    protected $table = 'investissements';
    protected $primaryKey = 'id_investissement';
    
    protected $fillable = [
        'reference', 'nom_projet', 'id_service', 'id_utilisateur', 'id_temps', 
        'montant', 'id_devise', 'description', 'duree_mois', 'roi_estime', 'statut'
    ];
    
    protected $casts = [
        'montant' => 'decimal:2',
        'roi_estime' => 'decimal:2',
        'duree_mois' => 'integer'
    ];
    
    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id_service');
    }
    
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
    
    public function temps()
    {
        return $this->belongsTo(Temps::class, 'id_temps', 'id_temps');
    }
    
    public function devise()
    {
        return $this->belongsTo(Devise::class, 'id_devise', 'id_devise');
    }
    
    public function estEnCours()
    {
        return $this->statut === 'En cours';
    }
    
    public function estTermine()
    {
        return $this->statut === 'Terminé';
    }
}