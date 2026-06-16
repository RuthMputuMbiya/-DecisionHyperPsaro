<?php
// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $roles = [
            [
                'nom_role' => 'Direction Générale',
                'code_role' => 'DG',
                'description' => 'Direction Générale - Accès complet au système',
                'niveau' => 1,
                'est_actif' => true,
            ],
            [
                'nom_role' => 'Conseiller Financier',
                'code_role' => 'CONSEILLER',
                'description' => 'Conseiller Financier - Analyse et simulations',
                'niveau' => 2,
                'est_actif' => true,
            ],
            [
                'nom_role' => 'Chef de Service',
                'code_role' => 'CHEF',
                'description' => 'Chef de Service - Gestion des opérations',
                'niveau' => 3,
                'est_actif' => true,
            ],
        ];
        
        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'nom_role' => $role['nom_role'],
                'code_role' => $role['code_role'],
                'description' => $role['description'],
                'niveau' => $role['niveau'],
                'est_actif' => $role['est_actif'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('✅ ' . count($roles) . ' rôles créés');
    }
}