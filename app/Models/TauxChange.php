<?php
// app/Models/TauxChange.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TauxChange extends Model
{
    protected $table = 'taux_change';
    protected $primaryKey = 'id_taux';
    
    protected $fillable = [
        'devise_source_id', 'devise_cible_id', 'taux', 'date_taux'
    ];
    
    protected $casts = [
        'taux' => 'decimal:6',
        'date_taux' => 'date'
    ];
    
    public function deviseSource()
    {
        return $this->belongsTo(Devise::class, 'devise_source_id', 'id_devise');
    }
    
    public function deviseCible()
    {
        return $this->belongsTo(Devise::class, 'devise_cible_id', 'id_devise');
    }
}