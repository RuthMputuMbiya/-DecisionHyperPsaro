<?php
// database/migrations/2024_01_01_000015_create_investissements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investissements', function (Blueprint $table) {
            $table->id('id_investissement');
            $table->string('reference', 50)->unique();
            $table->string('nom_projet', 100);
            $table->foreignId('id_service')->constrained('services', 'id_service');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->foreignId('id_temps')->constrained('temps', 'id_temps');
            $table->decimal('montant', 15, 2);
            $table->foreignId('id_devise')->constrained('devises', 'id_devise');
            $table->text('description')->nullable();
            $table->integer('duree_mois')->default(12);
            $table->decimal('roi_estime', 5, 2)->nullable();
            $table->string('statut', 20)->default('En cours');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investissements');
    }
};