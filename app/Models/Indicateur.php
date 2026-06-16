<?php
// app/Models/Indicateur.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicateur extends Model
{
    protected $table = 'indicateurs';
    protected $primaryKey = 'id_indicateur';
    
    protected $fillable = [
        'nom_kpi', 'code_kpi', 'unite', 'description', 
        'seuil_alerte_min', 'seuil_alerte_max', 'couleur_alerte', 'icone', 'est_actif'
    ];
    
    protected $casts = [
        'seuil_alerte_min' => 'decimal:2',
        'seuil_alerte_max' => 'decimal:2',
        'est_actif' => 'boolean'
    ];
    
    public function financeFaits()
    {
        return $this->hasMany(FinanceFait::class, 'id_indicateur', 'id_indicateur');
    }
    
    // Vérifier si une valeur est en alerte
    public function isAlerte($valeur)
    {
        if ($this->seuil_alerte_min && $valeur < $this->seuil_alerte_min) {
            return true;
        }
        if ($this->seuil_alerte_max && $valeur > $this->seuil_alerte_max) {
            return true;
        }
        return false;
    }
}