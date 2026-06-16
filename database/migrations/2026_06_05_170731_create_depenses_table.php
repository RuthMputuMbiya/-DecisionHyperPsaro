<?php
// database/migrations/2024_01_01_000014_create_depenses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id('id_depense');
            $table->string('reference', 50)->unique();
            $table->foreignId('id_service')->constrained('services', 'id_service');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->foreignId('id_temps')->constrained('temps', 'id_temps');
            $table->decimal('montant', 15, 2);
            $table->foreignId('id_devise')->constrained('devises', 'id_devise');
            $table->text('description')->nullable();
            $table->string('categorie_depense', 50);
            $table->string('justificatif')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('depenses');
    }
};