<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('achats', function (Blueprint $table) {
            $table->foreignId('fournisseur_id')->nullable()->after('id_devise')->constrained('fournisseurs')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('achats', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
            $table->dropColumn('fournisseur_id');
        });
    }
};