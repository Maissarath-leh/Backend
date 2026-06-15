<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned()->nullable();
            $table->dateTime('date_heure');
            $table->float('frequence_cardiaque')->nullable();
            $table->float('temperature')->nullable();
            $table->float('saturation_oxygene')->nullable();
            $table->float('tension_systolique')->nullable();
            $table->float('tension_diastolique')->nullable();
            $table->string('source')->default('simulateur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures');
    }
};