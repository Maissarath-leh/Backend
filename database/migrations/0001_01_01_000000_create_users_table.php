<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * C'est ici qu'on crée les tables dans la base de données.
     */
    public function up(): void
    {
        // 1. TABLE UTILISATEURS (La base pour Admin, Médecin et Patient)
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // idUtilisateur
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Mot de passe
            $table->string('telephone');
            
            // Ce champ est CRUCIAL : il définit qui est Admin, Médecin ou Patient
            $table->enum('role', ['patient', 'medecin', 'admin'])->default('patient');
            
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. TABLE REINITIALISATION MOT DE PASSE (Sécurité Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. TABLE SESSIONS (Pour rester connecté à l'application)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     * C'est ici qu'on définit comment supprimer les tables si besoin.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};