<?php
// database/seeders/IndicateurSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IndicateurSeeder extends Seeder
{
    /**
     * Liste des 15 indicateurs pré-définis avec leurs formules
     */
    protected $indicators = [
        // ============================================================
        // 1. INDICATEURS FINANCIERS
        // ============================================================
        [
            'code_kpi' => 'CA',
            'nom_kpi' => 'Chiffre d\'affaires',
            'unite' => 'USD',
            'icone' => 'fas fa-dollar-sign',
            'couleur_alerte' => '#1a56db',
            'formule' => 'SUM(ventes.montant_usd)',
            'seuil_alerte_min' => 10000,
            'seuil_alerte_max' => null,
            'description' => 'Total des ventes réalisées sur une période donnée',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'MB',
            'nom_kpi' => 'Marge brute',
            'unite' => 'USD',
            'icone' => 'fas fa-percent',
            'couleur_alerte' => '#0e9f6e',
            'formule' => 'SUM(ventes.montant_usd) - SUM(achats.montant_usd)',
            'seuil_alerte_min' => 5000,
            'seuil_alerte_max' => null,
            'description' => 'Différence entre le chiffre d\'affaires et le coût des achats',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'TMB',
            'nom_kpi' => 'Taux de marge brute',
            'unite' => '%',
            'icone' => 'fas fa-chart-pie',
            'couleur_alerte' => '#f5a623',
            'formule' => '((SUM(ventes.montant_usd) - SUM(achats.montant_usd)) / NULLIF(SUM(ventes.montant_usd), 0)) * 100',
            'seuil_alerte_min' => 20,
            'seuil_alerte_max' => 70,
            'description' => 'Pourcentage de marge réalisée sur les ventes',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'RN',
            'nom_kpi' => 'Résultat net',
            'unite' => 'USD',
            'icone' => 'fas fa-coins',
            'couleur_alerte' => '#7e3af2',
            'formule' => 'SUM(ventes.montant_usd) - SUM(achats.montant_usd) - SUM(depenses.montant_usd)',
            'seuil_alerte_min' => 3000,
            'seuil_alerte_max' => null,
            'description' => 'Bénéfice après déduction de toutes les charges',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'ROI',
            'nom_kpi' => 'Retour sur investissement',
            'unite' => '%',
            'icone' => 'fas fa-arrow-trend-up',
            'couleur_alerte' => '#10b981',
            'formule' => '((SUM(ventes.montant_usd) - SUM(investissements.montant_usd)) / NULLIF(SUM(investissements.montant_usd), 0)) * 100',
            'seuil_alerte_min' => 15,
            'seuil_alerte_max' => 100,
            'description' => 'Rentabilité des investissements réalisés',
            'est_actif' => true,
        ],
        
        // ============================================================
        // 2. INDICATEURS D'ACTIVITÉ
        // ============================================================
        [
            'code_kpi' => 'NVENTES',
            'nom_kpi' => 'Nombre de ventes',
            'unite' => 'Nombre',
            'icone' => 'fas fa-shopping-cart',
            'couleur_alerte' => '#3abff8',
            'formule' => 'COUNT(ventes.id_vente)',
            'seuil_alerte_min' => 50,
            'seuil_alerte_max' => null,
            'description' => 'Nombre total de transactions de vente',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'PANIER',
            'nom_kpi' => 'Panier moyen',
            'unite' => 'USD',
            'icone' => 'fas fa-shopping-basket',
            'couleur_alerte' => '#8b5cf6',
            'formule' => 'SUM(ventes.montant_usd) / NULLIF(COUNT(ventes.id_vente), 0)',
            'seuil_alerte_min' => 200,
            'seuil_alerte_max' => null,
            'description' => 'Montant moyen par transaction de vente',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'TCA',
            'nom_kpi' => 'Taux de conversion des achats',
            'unite' => '%',
            'icone' => 'fas fa-exchange-alt',
            'couleur_alerte' => '#f97316',
            'formule' => '(COUNT(ventes.id_vente) / NULLIF(COUNT(achats.id_achat), 0)) * 100',
            'seuil_alerte_min' => 30,
            'seuil_alerte_max' => 90,
            'description' => 'Proportion des achats qui génèrent des ventes',
            'est_actif' => true,
        ],
        
        // ============================================================
        // 3. INDICATEURS DE PERFORMANCE
        // ============================================================
        [
            'code_kpi' => 'CAP',
            'nom_kpi' => 'Capacité de production',
            'unite' => 'Nombre',
            'icone' => 'fas fa-industry',
            'couleur_alerte' => '#e02424',
            'formule' => 'SUM(investissements.quantite_produite)',
            'seuil_alerte_min' => 1000,
            'seuil_alerte_max' => null,
            'description' => 'Volume de production réalisé par les investissements',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'PERSO',
            'nom_kpi' => 'Productivité par personne',
            'unite' => 'USD',
            'icone' => 'fas fa-user-tie',
            'couleur_alerte' => '#1a56db',
            'formule' => 'SUM(ventes.montant_usd) / NULLIF(COUNT(DISTINCT utilisateurs.id_utilisateur), 0)',
            'seuil_alerte_min' => 5000,
            'seuil_alerte_max' => null,
            'description' => 'Chiffre d\'affaires généré par employé',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'DELAI',
            'nom_kpi' => 'Délai moyen de livraison',
            'unite' => 'Jours',
            'icone' => 'fas fa-clock',
            'couleur_alerte' => '#f5a623',
            'formule' => 'AVG(DATEDIFF(ventes.date_livraison, ventes.date_commande))',
            'seuil_alerte_min' => null,
            'seuil_alerte_max' => 5,
            'description' => 'Temps moyen entre la commande et la livraison',
            'est_actif' => true,
        ],
        
        // ============================================================
        // 4. INDICATEURS DE GESTION DES COÛTS
        // ============================================================
        [
            'code_kpi' => 'CDE',
            'nom_kpi' => 'Coût des dépenses',
            'unite' => 'USD',
            'icone' => 'fas fa-receipt',
            'couleur_alerte' => '#7e3af2',
            'formule' => 'SUM(depenses.montant_usd)',
            'seuil_alerte_min' => null,
            'seuil_alerte_max' => 8000,
            'description' => 'Total des dépenses engagées sur la période',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'RCD',
            'nom_kpi' => 'Ratio coûts/dépenses',
            'unite' => '%',
            'icone' => 'fas fa-calculator',
            'couleur_alerte' => '#0e9f6e',
            'formule' => '(SUM(depenses.montant_usd) / NULLIF(SUM(ventes.montant_usd), 0)) * 100',
            'seuil_alerte_min' => null,
            'seuil_alerte_max' => 60,
            'description' => 'Part des dépenses par rapport au chiffre d\'affaires',
            'est_actif' => true,
        ],
        
        // ============================================================
        // 5. INDICATEURS DE PERFORMANCE FINANCIÈRE
        // ============================================================
        [
            'code_kpi' => 'TRE',
            'nom_kpi' => 'Taux de rentabilité économique',
            'unite' => '%',
            'icone' => 'fas fa-chart-bar',
            'couleur_alerte' => '#10b981',
            'formule' => '((SUM(ventes.montant_usd) - SUM(achats.montant_usd) - SUM(depenses.montant_usd)) / NULLIF((SUM(achats.montant_usd) + SUM(depenses.montant_usd)), 0)) * 100',
            'seuil_alerte_min' => 10,
            'seuil_alerte_max' => 50,
            'description' => 'Rentabilité économique de l\'entreprise',
            'est_actif' => true,
        ],
        [
            'code_kpi' => 'BFRE',
            'nom_kpi' => 'Besoin en fonds de roulement',
            'unite' => 'USD',
            'icone' => 'fas fa-hand-holding-usd',
            'couleur_alerte' => '#e02424',
            'formule' => 'SUM(ventes.montant_usd) - SUM(achats.montant_usd)',
            'seuil_alerte_min' => 2000,
            'seuil_alerte_max' => 50000,
            'description' => 'Fonds nécessaires pour financer l\'activité courante',
            'est_actif' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider la table avant d'insérer (optionnel)
        // DB::table('indicateurs')->truncate();
        
        $this->command->info('🚀 Début de l\'insertion des indicateurs pré-définis...');
        
        $count = 0;
        
        foreach ($this->indicators as $indicator) {
            // Vérifier si l'indicateur existe déjà
            $exists = DB::table('indicateurs')
                ->where('code_kpi', $indicator['code_kpi'])
                ->exists();
            
            if (!$exists) {
                // Ajouter les timestamps
                $indicator['created_at'] = now();
                $indicator['updated_at'] = now();
                
                // Insérer dans la base de données
                DB::table('indicateurs')->insert($indicator);
                $count++;
                
                $this->command->info("✅ Indicateur créé : {$indicator['code_kpi']} - {$indicator['nom_kpi']}");
            } else {
                // Mettre à jour si déjà existant
                DB::table('indicateurs')
                    ->where('code_kpi', $indicator['code_kpi'])
                    ->update(array_merge($indicator, [
                        'updated_at' => now()
                    ]));
                
                $this->command->info("🔄 Indicateur mis à jour : {$indicator['code_kpi']} - {$indicator['nom_kpi']}");
            }
        }
        
        // Afficher le résumé
        $this->command->newLine();
        $this->command->info('✅ ========================================');
        $this->command->info("✅ {$count} NOUVEAUX indicateurs pré-définis créés avec succès !");
        $this->command->info('✅ Total : ' . count($this->indicators) . ' indicateurs dans la base');
        $this->command->info('✅ ========================================');
        $this->command->newLine();
        
        // Afficher la liste des indicateurs
        $this->command->table(
            ['Code', 'Indicateur', 'Unité', 'Seuil Min', 'Seuil Max'],
            array_map(function($item) {
                return [
                    $item['code_kpi'],
                    $item['nom_kpi'],
                    $item['unite'],
                    $item['seuil_alerte_min'] ?? '-',
                    $item['seuil_alerte_max'] ?? '-',
                ];
            }, $this->indicators)
        );
    }
    
    /**
     * Méthode pour récupérer les indicateurs (utilisée par le contrôleur)
     */
    public static function getPredefinedIndicators(): array
    {
        return (new static())->indicators;
    }
}