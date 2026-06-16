<?php
// database/migrations/2024_01_01_000009_create_finance_faits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finance_faits', function (Blueprint $table) {
            $table->id('id_fait');
            $table->decimal('montant_original', 15, 2);
            $table->decimal('montant_cdf', 15, 2);
            $table->decimal('montant_usd', 15, 2);
            $table->decimal('montant_eur', 15, 2);
            $table->text('description')->nullable();
            
            $table->foreignId('id_temps')->constrained('temps', 'id_temps');
            $table->foreignId('id_service')->constrained('services', 'id_service');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->foreignId('id_indicateur')->constrained('indicateurs', 'id_indicateur');
            $table->foreignId('id_activite')->constrained('activites', 'id_activite');
            $table->foreignId('id_devise_originale')->constrained('devises', 'id_devise');
            
            $table->timestamps();
            
            $table->index(['id_temps', 'id_service']);
            $table->index(['id_indicateur', 'id_activite']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_faits');
    }
};