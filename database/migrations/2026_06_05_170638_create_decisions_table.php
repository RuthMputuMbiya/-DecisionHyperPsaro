<?php
// database/migrations/2024_01_01_000010_create_decisions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id('id_decision');
            $table->string('titre', 200);
            $table->text('description')->nullable();
            $table->json('parametres');
            $table->decimal('resultat_simule', 15, 2);
            $table->decimal('impact_tresorerie', 15, 2);
            $table->text('decision_prise');
            $table->foreignId('cree_par')->constrained('utilisateurs', 'id_utilisateur');
            $table->enum('statut', ['En attente', 'Validée', 'Rejetée', 'En cours'])->default('En attente');
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('decisions');
    }
};