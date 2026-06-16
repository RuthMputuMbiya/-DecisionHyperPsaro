<?php
// app/Models/Devise.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devise extends Model
{
    protected $table = 'devises';
    protected $primaryKey = 'id_devise';
    
    protected $fillable = [
        'code', 'nom', 'symbole', 'taux_cdf', 'taux_usd', 'taux_eur', 'date_taux'
    ];
    
    protected $casts = [
        'taux_cdf' => 'decimal:6',
        'taux_usd' => 'decimal:6',
        'taux_eur' => 'decimal:6',
        'date_taux' => 'date'
    ];
    
    public function financeFaits()
    {
        return $this->hasMany(FinanceFait::class, 'id_devise_originale', 'id_devise');
    }
    
    public function tauxSources()
    {
        return $this->hasMany(TauxChange::class, 'devise_source_id', 'id_devise');
    }
    
    public function tauxCibles()
    {
        return $this->hasMany(TauxChange::class, 'devise_cible_id', 'id_devise');
    }
    
    // Convertir un montant depuis cette devise vers une autre
    public function convertir($montant, $deviseCibleCode)
    {
        $deviseCible = self::where('code', $deviseCibleCode)->first();
        if (!$deviseCible) {
            return $montant;
        }
        
        // Conversion via CDF comme pivot
        $montantEnCDF = $montant * $this->taux_cdf;
        return $montantEnCDF / $deviseCible->taux_cdf;
    }
}