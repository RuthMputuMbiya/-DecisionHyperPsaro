<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seulement les seeders qui existent
        $this->call(RoleSeeder::class);
        // $this->call(ServiceSeeder::class);  // COMMENTÉ car n'existe pas
        // $this->call(ActiviteSeeder::class); // COMMENTÉ
        // $this->call(DeviseSeeder::class);   // COMMENTÉ
        // $this->call(TempsSeeder::class);    // COMMENTÉ
        $this->call(DgUserSeeder::class);
    }
}