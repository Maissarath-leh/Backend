<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesure extends Model
{
    protected $fillable = [
        'patient_id',
        'date_heure',
        'frequence_cardiaque',
        'temperature',
        'saturation_oxygene',
        'tension_systolique',
        'tension_diastolique',
        'source',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}