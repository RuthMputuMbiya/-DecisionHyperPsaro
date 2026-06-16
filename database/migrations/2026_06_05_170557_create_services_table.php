<?php
// database/migrations/2024_01_01_000002_create_services_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id('id_service');
            $table->string('nom_service', 100);
            $table->text('description')->nullable();
            $table->string('chef_service')->nullable();
            $table->decimal('budget_annuel', 15, 2)->default(0);
            $table->string('couleur', 20)->default('#3498db');
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};