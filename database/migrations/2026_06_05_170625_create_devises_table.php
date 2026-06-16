<?php
// database/migrations/2024_01_01_000007_create_devises_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devises', function (Blueprint $table) {
            $table->id('id_devise');
            $table->string('code', 3)->unique();
            $table->string('nom', 50);
            $table->string('symbole', 10);
            $table->decimal('taux_cdf', 15, 6);
            $table->decimal('taux_usd', 15, 6);
            $table->decimal('taux_eur', 15, 6);
            $table->date('date_taux');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devises');
    }
};