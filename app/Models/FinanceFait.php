<?php
// app/Models/FinanceFait.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceFait extends Model
{
    protected $table = 'finance_faits';
    protected $primaryKey = 'id_fait';
    
    protected $fillable = [
        'montant_original', 'montant_cdf', 'montant_usd', 'montant_eur', 'description',
        'id_temps', 'id_service', 'id_utilisateur', 'id_indicateur', 'id_activite', 'id_devise_originale'
    ];
    
    protected $casts = [
        'montant_original' => 'decimal:2',
        'montant_cdf' => 'decimal:2',
        'montant_usd' => 'decimal:2',
        'montant_eur' => 'decimal:2',
    ];
    
    // Relations
    public function temps()
    {
        return $this->belongsTo(Temps::class, 'id_temps', 'id_temps');
    }
    
    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id_service');
    }
    
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
    
    public function indicateur()
    {
        return $this->belongsTo(Indicateur::class, 'id_indicateur', 'id_indicateur');
    }
    
    public function activite()
    {
        return $this->belongsTo(Activite::class, 'id_activite', 'id_activite');
    }
    
    public function deviseOriginale()
    {
        return $this->belongsTo(Devise::class, 'id_devise_originale', 'id_devise');
    }
    
    // Récupérer le montant dans une devise spécifique
    public function getMontant($deviseCode)
    {
        $colonne = 'montant_' . strtolower($deviseCode);
        return $this->$colonne ?? $this->montant_usd;
    }
}