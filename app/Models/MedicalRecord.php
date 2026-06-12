<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'tension',
        'pouls',
        'poids',
        'temperature',
        'glycemie',
        'spo2',
        'blood_type',
        'allergies',
        'antecedents',
        'diagnosis',
        'treatment',
        'prescriptions',
        'hospitalizations',
        'notes',
        'next_appointment',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}