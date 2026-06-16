<?php
// database/seeders/DeviseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeviseSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('devises')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $devises = [
            [
                'code' => 'CDF',
                'nom' => 'Franc Congolais',
                'symbole' => 'FC',
                'taux_cdf' => 1,
                'taux_usd' => 0.00037,
                'taux_eur' => 0.00034,
                'date_taux' => now(),
            ],
            [
                'code' => 'USD',
                'nom' => 'Dollar US',
                'symbole' => '$',
                'taux_cdf' => 2700,
                'taux_usd' => 1,
                'taux_eur' => 0.92,
                'date_taux' => now(),
            ],
            [
                'code' => 'EUR',
                'nom' => 'Euro',
                'symbole' => '€',
                'taux_cdf' => 2900,
                'taux_usd' => 1.08,
                'taux_eur' => 1,
                'date_taux' => now(),
            ],
        ];
        
        foreach ($devises as $devise) {
            DB::table('devises')->insert([
                'code' => $devise['code'],
                'nom' => $devise['nom'],
                'symbole' => $devise['symbole'],
                'taux_cdf' => $devise['taux_cdf'],
                'taux_usd' => $devise['taux_usd'],
                'taux_eur' => $devise['taux_eur'],
                'date_taux' => $devise['date_taux'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('✅ ' . count($devises) . ' devises créées');
    }
}