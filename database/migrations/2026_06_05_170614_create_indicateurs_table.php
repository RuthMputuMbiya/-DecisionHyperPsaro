<?php
// database/migrations/2024_01_01_000005_create_indicateurs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indicateurs', function (Blueprint $table) {
            $table->id('id_indicateur');
            $table->string('nom_kpi', 100);
            $table->string('code_kpi', 20)->unique();
            $table->string('unite', 20);
            $table->text('description')->nullable();
            $table->decimal('seuil_alerte_min', 15, 2)->nullable();
            $table->decimal('seuil_alerte_max', 15, 2)->nullable();
            $table->string('couleur_alerte', 20)->default('#ff6b6b');
            $table->string('icone', 50)->default('fas fa-chart-line');
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indicateurs');
    }
};