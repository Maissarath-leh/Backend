<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('medecin_id')->constrained('medecins')->onDelete('cascade');

            // CONSTANTES VITALES
            $table->string('tension')->nullable();
            $table->string('pouls')->nullable();
            $table->string('poids')->nullable();
            $table->string('temperature')->nullable();
            $table->string('glycemie')->nullable();
            $table->string('spo2')->nullable();

            // INFORMATIONS MÉDICALES
            $table->string('blood_type')->nullable();
            $table->text('allergies')->nullable();
            $table->text('antecedents')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('hospitalizations')->nullable();
            $table->text('notes')->nullable();

            // AUTRES
            $table->date('next_appointment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};