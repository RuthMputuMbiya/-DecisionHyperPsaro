<?php
// database/seeders/ActiviteSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActiviteSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('activites')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $activites = [
            ['type_activite' => 'vente', 'categorie' => 'Revenue', 'icone' => 'fas fa-shopping-cart'],
            ['type_activite' => 'achat', 'categorie' => 'Dépense', 'icone' => 'fas fa-truck'],
            ['type_activite' => 'depense', 'categorie' => 'Dépense', 'icone' => 'fas fa-receipt'],
            ['type_activite' => 'investissement', 'categorie' => 'Investissement', 'icone' => 'fas fa-chart-line'],
        ];
        
        foreach ($activites as $activite) {
            DB::table('activites')->insert([
                'type_activite' => $activite['type_activite'],
                'categorie' => $activite['categorie'],
                'icone' => $activite['icone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('✅ ' . count($activites) . ' activités créées');
    }
}