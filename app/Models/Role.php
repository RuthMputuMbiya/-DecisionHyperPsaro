<?php
// app/Models/Role.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    
    protected $fillable = [
        'nom_role', 'code_role', 'description', 'niveau', 'est_actif'
    ];
    
    protected $casts = [
        'est_actif' => 'boolean',
        'niveau' => 'integer'
    ];
    
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'role_id', 'id_role');
    }
    
    public function isDG()
    {
        return $this->code_role === 'DG';
    }
    
    public function isConseiller()
    {
        return $this->code_role === 'CONSEILLER';
    }
    
    public function isChef()
    {
        return $this->code_role === 'CHEF';
    }
}