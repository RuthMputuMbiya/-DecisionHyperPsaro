<?php
// database/migrations/2024_01_01_000004_create_temps_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('temps', function (Blueprint $table) {
            $table->id('id_temps');
            $table->date('date');
            $table->tinyInteger('jour');
            $table->tinyInteger('mois');
            $table->tinyInteger('trimestre');
            $table->smallInteger('annee');
            $table->string('type_periode', 20)->nullable();
            $table->boolean('est_cloturee')->default(false);
            $table->timestamps();
            
            $table->index(['annee', 'mois']);
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('temps');
    }
};