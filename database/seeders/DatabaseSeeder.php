<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seulement les seeders qui existent
        $this->call(RoleSeeder::class);
        $this->call(DgUserSeeder::class);
        
        // 🔥 Ajout du seeder des indicateurs
        $this->call(IndicateurSeeder::class);
        
        // Les autres seeders sont commentés car ils n'existent pas encore
        // $this->call(ServiceSeeder::class);
        // $this->call(ActiviteSeeder::class);
        // $this->call(DeviseSeeder::class);
        // $this->call(TempsSeeder::class);
        
        $this->command->info('✅ Tous les seeders ont été exécutés avec succès !');
    }
}