<?php
// database/migrations/2024_01_01_000003_create_utilisateurs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('matricule', 20)->unique(); // 🔥 Matricule unique auto-généré
            $table->string('nom', 100);
            $table->string('email', 100)->unique();
            $table->string('mot_de_passe');
            $table->foreignId('role_id')->constrained('roles', 'id_role')->onDelete('restrict');
            $table->foreignId('service_id')->nullable()->constrained('services', 'id_service')->onDelete('set null');
            $table->string('telephone', 20)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->boolean('premiere_connexion')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_password_change')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Index pour recherche par matricule ou email
            $table->index('matricule');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('utilisateurs');
    }
};