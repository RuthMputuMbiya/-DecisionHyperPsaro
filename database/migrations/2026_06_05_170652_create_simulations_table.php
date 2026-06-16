<?php
// database/migrations/2024_01_01_000011_create_simulations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id('id_simulation');
            $table->string('nom_simulation', 100);
            $table->text('description')->nullable();
            $table->json('parametres_saisis');
            $table->json('resultats');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->foreignId('id_decision')->nullable()->constrained('decisions', 'id_decision');
            $table->boolean('est_sauvegardee')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('simulations');
    }
};