<?php
// app/Models/Depense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    protected $table = 'depenses';
    protected $primaryKey = 'id_depense';
    
    protected $fillable = [
        'reference', 'id_service', 'id_utilisateur', 'id_temps', 
        'montant', 'id_devise', 'description', 'categorie_depense', 'justificatif'
    ];
    
    protected $casts = [
        'montant' => 'decimal:2'
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
}