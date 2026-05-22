<?php

namespace App\Http\Controllers;

use App\Models\Mesure;
use App\Models\Alerte;
use App\Models\Patient;
use Illuminate\Http\Request;

class SimulateurController extends Controller
{
    public function generer(Request $request)
    {
        $patient = Patient::inRandomOrder()->first();

        if (!$patient) {
            return response()->json(['message' => 'Aucun patient trouvé.'], 404);
        }

        $fc = rand(48, 115);
        $temp = round(36.0 + mt_rand(0, 25) / 10, 1);
        $spo2 = rand(88, 100);
        $sys = rand(90, 180);
        $dia = rand(50, min(120, $sys - 10));

        $mesure = Mesure::create([
            'patient_id' => $patient->id,
            'date_heure' => now(),
            'frequence_cardiaque' => $fc,
            'temperature' => $temp,
            'saturation_oxygene' => $spo2,
            'tension_systolique' => $sys,
            'tension_diastolique' => $dia,
            'source' => 'simulateur',
        ]);

        $alertes = $this->analyserMesure($patient, $mesure, $fc, $temp, $spo2, $sys, $dia);

        return response()->json([
            'mesure' => $mesure,
            'alertes' => $alertes,
        ]);
    }

    private function analyserMesure($patient, $mesure, $fc, $temp, $spo2, $sys, $dia)
    {
        $alertes = [];

        if ($fc < 55) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'frequence_cardiaque', 'niveau' => 'critique',
                'message' => "Bradycardie détectée : FC à $fc bpm", 'vue' => false,
            ]);
        } elseif ($fc > 105) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'frequence_cardiaque', 'niveau' => 'critique',
                'message' => "Tachycardie détectée : FC à $fc bpm", 'vue' => false,
            ]);
        }

        if ($temp > 38.0) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'temperature', 'niveau' => 'critique',
                'message' => "Fièvre détectée : $temp °C", 'vue' => false,
            ]);
        }

        if ($spo2 < 93) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'saturation_oxygene', 'niveau' => 'critique',
                'message' => "Désaturation détectée : SpO₂ à $spo2 %", 'vue' => false,
            ]);
        }

        if ($sys > 140 || $dia > 90) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'tension', 'niveau' => 'critique',
                'message' => "Hypertension détectée : $sys/$dia mmHg", 'vue' => false,
            ]);
        } elseif ($sys < 90 || $dia < 50) {
            $alertes[] = Alerte::create([
                'patient_id' => $patient->id, 'mesure_id' => $mesure->id,
                'type' => 'tension', 'niveau' => 'critique',
                'message' => "Hypotension détectée : $sys/$dia mmHg", 'vue' => false,
            ]);
        }

        return $alertes;
    }
}