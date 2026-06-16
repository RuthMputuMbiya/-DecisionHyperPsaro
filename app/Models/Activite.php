<?php
// app/Models/Activite.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
    protected $table = 'activites';
    protected $primaryKey = 'id_activite';
    
    protected $fillable = [
        'type_activite', 'categorie', 'icone', 'description'
    ];
    
    public function financeFaits()
    {
        return $this->hasMany(FinanceFait::class, 'id_activite', 'id_activite');
    }
    
    public function isVente()
    {
        return $this->type_activite === 'vente';
    }
    
    public function isAchat()
    {
        return $this->type_activite === 'achat';
    }
    
    public function isDepense()
    {
        return $this->type_activite === 'depense';
    }
    
    public function isInvestissement()
    {
        return $this->type_activite === 'investissement';
    }
}