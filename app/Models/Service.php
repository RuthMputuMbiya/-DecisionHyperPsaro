<?php
// app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id_service';
    
    protected $fillable = [
        'nom_service', 'description', 'chef_service', 'budget_annuel', 
        'budget_devise', 'couleur', 'est_actif'
    ];
    
    protected $casts = [
        'budget_annuel' => 'decimal:2',
        'est_actif' => 'boolean'
    ];
    
    // Accessoire pour afficher le budget avec sa devise
    public function getBudgetFormattedAttribute()
    {
        $symbole = '';
        switch ($this->budget_devise) {
            case 'USD':
                $symbole = '$';
                break;
            case 'CDF':
                $symbole = 'FC';
                break;
            case 'EUR':
                $symbole = '€';
                break;
            default:
                $symbole = '$';
        }
        return $symbole . ' ' . number_format($this->budget_annuel, 0, ',', ' ');
    }
    
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'service_id', 'id_service');
    }
    
    public function financeFaits()
    {
        return $this->hasMany(FinanceFait::class, 'id_service', 'id_service');
    }
}