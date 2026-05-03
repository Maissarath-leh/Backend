<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id(); // idPatient
            $table->date('date_naissance');
            $table->enum('sexe', ['M', 'F']); // Masculin ou Féminin
            $table->text('adresse');

            // Clé étrangère vers 'users' (pour le nom, prénom, tel, email)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Clé étrangère vers 'medecins' (le médecin qui suit ce patient)
            // 'nullable' car un patient n'a peut-être pas encore de médecin affecté
            $table->foreignId('medecin_id')->nullable()->constrained('medecins')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};