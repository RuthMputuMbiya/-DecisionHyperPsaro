<?php
// database/seeders/DgUserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DgUserSeeder extends Seeder
{
    public function run()
    {
        // Récupérer l'ID du rôle DG
        $roleDG = DB::table('roles')->where('code_role', 'DG')->first();
        
        if (!$roleDG) {
            $this->command->error('❌ Rôle DG non trouvé ! Exécutez d\'abord RoleSeeder');
            return;
        }
        
        $this->command->info('✅ Rôle DG trouvé avec ID : ' . $roleDG->id_role);
        
        // ==============================================
        // 1. DIRECTEUR GÉNÉRAL PRINCIPAL
        // ==============================================
        // 🔥 Supprimer l'ancien utilisateur s'il existe
        DB::table('utilisateurs')->where('email', 'direction@gmail.com')->delete();
        
        DB::table('utilisateurs')->insert([
            'matricule' => 'DG2024001',
            'nom' => 'Directeur Général',
            'email' => 'direction@gmail.com',
            'mot_de_passe' => Hash::make('password123'),
            'role_id' => $roleDG->id_role,
            'service_id' => null,
            'telephone' => '+243 812345678',
            'photo' => null,
            'est_actif' => true,
            'premiere_connexion' => false, // 🔥 PAS de changement de mot de passe
            'last_login_at' => null,
            'last_password_change' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('✅ Directeur Général Principal créé');
        $this->command->info('   📧 Email: direction@gmail.com');
        $this->command->info('   🆔 Matricule: DG2024001');
        $this->command->info('   🔑 Mot de passe: password123');
        $this->command->info('   🟢 Première connexion: NON (accès direct)');
        
        // ==============================================
        // 2. DIRECTEUR GÉNÉRAL ADJOINT
        // ==============================================
        DB::table('utilisateurs')->where('email', 'direction1@gmail.com')->delete();
        
        DB::table('utilisateurs')->insert([
            'matricule' => 'DG2024002',
            'nom' => 'Directeur Général Adjoint',
            'email' => 'direction1@gmail.com',
            'mot_de_passe' => Hash::make('password123'),
            'role_id' => $roleDG->id_role,
            'service_id' => null,
            'telephone' => '+243 812345679',
            'photo' => null,
            'est_actif' => true,
            'premiere_connexion' => false, // 🔥 PAS de changement de mot de passe
            'last_login_at' => null,
            'last_password_change' => null,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('✅ Directeur Général Adjoint créé');
        $this->command->info('   📧 Email: direction1@gmail.com');
        $this->command->info('   🆔 Matricule: DG2024002');
        $this->command->info('   🔑 Mot de passe: password123');
        $this->command->info('   🟢 Première connexion: NON (accès direct)');
        
        $this->command->info('=========================================');
        $this->command->info('🔐 RÉSUMÉ DES IDENTIFIANTS DG :');
        $this->command->info('   1. direction@gmail.com / password123');
        $this->command->info('   2. direction1@gmail.com / password123');
        $this->command->info('=========================================');
        $this->command->info('📌 IMPORTANT :');
        $this->command->info('   - La Direction Générale se connecte DIRECTEMENT');
        $this->command->info('   - Pas de changement de mot de passe obligatoire');
        $this->command->info('   - Les autres utilisateurs (Conseiller, Chef)');
        $this->command->info('     créés par la DG devront changer leur mot de passe');
        $this->command->info('=========================================');
    }
}