<?php
// database/migrations/2026_01_01_000000_add_formule_to_indicateurs.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormuleToIndicateurs extends Migration
{
    public function up()
    {
        Schema::table('indicateurs', function (Blueprint $table) {
            $table->text('formule')->nullable()->after('icone');
        });
    }

    public function down()
    {
        Schema::table('indicateurs', function (Blueprint $table) {
            $table->dropColumn('formule');
        });
    }
}