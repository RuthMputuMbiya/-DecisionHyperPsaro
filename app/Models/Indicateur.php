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
        'seuil_alerte_min', 'seuil_alerte_max', 'couleur_alerte', 
        'icone', 'est_actif', 'formule', 'dependances'
    ];
    
    protected $casts = [
        'seuil_alerte_min' => 'decimal:2',
        'seuil_alerte_max' => 'decimal:2',
        'est_actif' => 'boolean'
    ];
    
    /**
     * Indicateurs pré-définis avec leurs formules
     */
    public static function getPredefinedIndicators()
    {
        return [
            [
                'nom_kpi' => 'Chiffre d\'Affaires',
                'code_kpi' => 'CA',
                'unite' => 'USD',
                'formule' => 'SUM(ventes.montant)',
                'description' => 'Total des ventes réalisées sur une période donnée',
                'seuil_alerte_min' => 10000,
                'seuil_alerte_max' => 1000000,
                'icone' => 'fas fa-chart-line',
                'couleur_alerte' => '#1B6EF3'
            ],
            [
                'nom_kpi' => 'Marge Brute',
                'code_kpi' => 'MARGE',
                'unite' => 'USD',
                'formule' => 'CA - SUM(achats.montant)',
                'description' => 'Différence entre le chiffre d\'affaires et le coût des achats',
                'seuil_alerte_min' => 5000,
                'seuil_alerte_max' => 500000,
                'icone' => 'fas fa-percent',
                'couleur_alerte' => '#0e9f6e'
            ],
            [
                'nom_kpi' => 'Taux de Marge',
                'code_kpi' => 'TX_MARGE',
                'unite' => '%',
                'formule' => '(MARGE / CA) * 100',
                'description' => 'Pourcentage de marge par rapport au chiffre d\'affaires',
                'seuil_alerte_min' => 15,
                'seuil_alerte_max' => 50,
                'icone' => 'fas fa-percent',
                'couleur_alerte' => '#f5a623'
            ],
            [
                'nom_kpi' => 'Trésorerie',
                'code_kpi' => 'TRESO',
                'unite' => 'USD',
                'formule' => 'CA - (depenses.montant + achats.montant + investissements.montant)',
                'description' => 'Disponibilités financières de l\'entreprise',
                'seuil_alerte_min' => 50000,
                'seuil_alerte_max' => 2000000,
                'icone' => 'fas fa-coins',
                'couleur_alerte' => '#f5a623'
            ],
            [
                'nom_kpi' => 'Résultat Net',
                'code_kpi' => 'RN',
                'unite' => 'USD',
                'formule' => 'CA - (depenses.montant + achats.montant + investissements.montant)',
                'description' => 'Bénéfice ou perte après toutes les charges',
                'seuil_alerte_min' => 10000,
                'seuil_alerte_max' => 500000,
                'icone' => 'fas fa-chart-pie',
                'couleur_alerte' => '#7e3af2'
            ],
            [
                'nom_kpi' => 'ROI (Retour Invest.)',
                'code_kpi' => 'ROI',
                'unite' => '%',
                'formule' => '((CA - investissements.montant) / investissements.montant) * 100',
                'description' => 'Retour sur investissement',
                'seuil_alerte_min' => 10,
                'seuil_alerte_max' => 50,
                'icone' => 'fas fa-chart-simple',
                'couleur_alerte' => '#10b981'
            ],
            [
                'nom_kpi' => 'Seuil de Rentabilité',
                'code_kpi' => 'SEUIL',
                'unite' => 'USD',
                'formule' => 'depenses.montant / (MARGE / CA)',
                'description' => 'Chiffre d\'affaires minimum pour être rentable',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-bullseye',
                'couleur_alerte' => '#3abff8'
            ],
            [
                'nom_kpi' => 'Péage',
                'code_kpi' => 'PEAGE',
                'unite' => 'USD',
                'formule' => 'SUM(peage.montant)',
                'description' => 'Total des frais de péage enregistrés',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-road',
                'couleur_alerte' => '#e02424'
            ],
            [
                'nom_kpi' => 'Dépenses Opérationnelles',
                'code_kpi' => 'DEP_OP',
                'unite' => 'USD',
                'formule' => 'SUM(depenses.montant)',
                'description' => 'Charges liées aux opérations courantes',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => 1000000,
                'icone' => 'fas fa-receipt',
                'couleur_alerte' => '#e02424'
            ],
            [
                'nom_kpi' => 'Investissements',
                'code_kpi' => 'INVEST',
                'unite' => 'USD',
                'formule' => 'SUM(investissements.montant)',
                'description' => 'Total des investissements réalisés',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-chart-line',
                'couleur_alerte' => '#7e3af2'
            ],
            [
                'nom_kpi' => 'Nombre de Transactions',
                'code_kpi' => 'NB_TRANS',
                'unite' => 'Nombre',
                'formule' => 'COUNT(ventes) + COUNT(achats) + COUNT(depenses)',
                'description' => 'Volume total des opérations enregistrées',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-exchange-alt',
                'couleur_alerte' => '#1B6EF3'
            ],
            [
                'nom_kpi' => 'Panier Moyen',
                'code_kpi' => 'PANIER',
                'unite' => 'USD',
                'formule' => 'CA / COUNT(ventes)',
                'description' => 'Montant moyen par vente',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-shopping-cart',
                'couleur_alerte' => '#0e9f6e'
            ],
            [
                'nom_kpi' => 'Croissance',
                'code_kpi' => 'CROISSANCE',
                'unite' => '%',
                'formule' => '((CA_N - CA_N-1) / CA_N-1) * 100',
                'description' => 'Taux de croissance du chiffre d\'affaires',
                'seuil_alerte_min' => 5,
                'seuil_alerte_max' => 20,
                'icone' => 'fas fa-arrow-up',
                'couleur_alerte' => '#0e9f6e'
            ],
            [
                'nom_kpi' => 'Rotation des Stocks',
                'code_kpi' => 'ROT_STOCK',
                'unite' => 'Jours',
                'formule' => '(Stock Moyen / CA) * 365',
                'description' => 'Délai moyen de rotation des stocks',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-boxes',
                'couleur_alerte' => '#f5a623'
            ],
            [
                'nom_kpi' => 'Rentabilité des Actifs',
                'code_kpi' => 'RENT_ACTIF',
                'unite' => '%',
                'formule' => '(RN / Total Actifs) * 100',
                'description' => 'Rentabilité des actifs de l\'entreprise',
                'seuil_alerte_min' => null,
                'seuil_alerte_max' => null,
                'icone' => 'fas fa-building',
                'couleur_alerte' => '#7e3af2'
            ]
        ];
    }
}