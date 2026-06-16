<?php
// database/seeders/TempsSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TempsSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('temps')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $startDate = Carbon::create(2023, 1, 1);
        $endDate = Carbon::create(2025, 12, 31);
        
        $currentDate = $startDate->copy();
        $count = 0;
        
        while ($currentDate <= $endDate) {
            DB::table('temps')->insert([
                'date' => $currentDate,
                'jour' => $currentDate->day,
                'mois' => $currentDate->month,
                'trimestre' => ceil($currentDate->month / 3),
                'annee' => $currentDate->year,
                'type_periode' => $currentDate->isWeekend() ? 'weekend' : 'ouvre',
                'est_cloturee' => $currentDate < now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $currentDate->addDay();
            $count++;
        }
        
        $this->command->info("✅ $count jours générés");
    }
}