<?php
// database/migrations/2024_01_01_000006_create_activites_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id('id_activite');
            $table->string('type_activite', 50);
            $table->string('categorie', 50);
            $table->string('icone', 50)->default('fas fa-chart-line');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activites');
    }
};