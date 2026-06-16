<?php
// app/Models/Temps.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temps extends Model
{
    protected $table = 'temps';
    protected $primaryKey = 'id_temps';
    
    protected $fillable = [
        'date', 'jour', 'mois', 'trimestre', 'annee', 'type_periode', 'est_cloturee'
    ];
    
    protected $casts = [
        'date' => 'date',
        'est_cloturee' => 'boolean'
    ];
    
    public function financeFaits()
    {
        return $this->hasMany(FinanceFait::class, 'id_temps', 'id_temps');
    }
    
    public function ventes()
    {
        return $this->hasMany(Vente::class, 'id_temps', 'id_temps');
    }
    
    public function achats()
    {
        return $this->hasMany(Achat::class, 'id_temps', 'id_temps');
    }
    
    public function depenses()
    {
        return $this->hasMany(Depense::class, 'id_temps', 'id_temps');
    }
    
    public function investissements()
    {
        return $this->hasMany(Investissement::class, 'id_temps', 'id_temps');
    }
    
    // Accesseur pour le libellé
    public function getLibelleAttribute()
    {
        return $this->jour . '/' . $this->mois . '/' . $this->annee;
    }
}