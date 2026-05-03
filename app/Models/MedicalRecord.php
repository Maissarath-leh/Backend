<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    // Colonnes que l’on peut remplir en masse
    protected $fillable = [
        'patient_id',
        'medecin_id',
        'blood_type',
        'allergies',
        'antecedents',
        'diagnosis',
        'treatment',
        'prescriptions',
        'hospitalizations',
        'notes',
    ];

    /**
     * Relation avec le modèle Patient
     * Chaque dossier médical appartient à un patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation avec le modèle Medecin
     * Chaque dossier médical est créé par un médecin.
     */
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}
