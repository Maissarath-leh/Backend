<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    protected $fillable = [
        'patient_id',
        'mesure_id',
        'type',
        'niveau',
        'message',
        'vue',
    ];

    protected $casts = [
        'vue' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function mesure()
    {
        return $this->belongsTo(Mesure::class);
    }
}