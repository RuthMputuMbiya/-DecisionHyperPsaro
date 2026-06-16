<?php
// database/migrations/2024_01_01_000008_create_taux_change_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('taux_change', function (Blueprint $table) {
            $table->id('id_taux');
            $table->foreignId('devise_source_id')->constrained('devises', 'id_devise');
            $table->foreignId('devise_cible_id')->constrained('devises', 'id_devise');
            $table->decimal('taux', 15, 6);
            $table->date('date_taux');
            $table->timestamps();
            
            $table->unique(['devise_source_id', 'devise_cible_id', 'date_taux']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('taux_change');
    }
};