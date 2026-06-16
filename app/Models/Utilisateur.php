<?php
// app/Models/Utilisateur.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';
    
    protected $fillable = [
        'matricule', 'nom', 'email', 'mot_de_passe', 'role_id', 
        'service_id', 'telephone', 'est_actif', 'premiere_connexion',
        'last_login_at', 'last_password_change'
    ];
    
    protected $hidden = [
        'mot_de_passe', 'remember_token',
    ];
    
    protected $casts = [
        'est_actif' => 'boolean',
        'premiere_connexion' => 'boolean',
        'last_login_at' => 'datetime',
        'last_password_change' => 'datetime',
    ];
    
    // 🔥 IMPORTANT : Indiquer à Laravel quel champ utiliser pour le mot de passe
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }
    
    // Relations
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role');
    }
    
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id_service');
    }
    
    // Vérification des rôles
    public function isDG()
    {
        return $this->role && $this->role->code_role == 'DG';
    }
    
    public function isConseiller()
    {
        return $this->role && $this->role->code_role == 'CONSEILLER';
    }
    
    public function isChef()
    {
        return $this->role && $this->role->code_role == 'CHEF';
    }
}