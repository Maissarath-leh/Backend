<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ordonnance extends Model
{
    protected $fillable = [
        'medecin_id',
        'patient_id',
        'pharmacie_id',
        'contenu',
        'statut',
        'motif_refus',
    ];

    protected $casts = [
        'contenu' => 'array',
    ];

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function pharmacie(): BelongsTo
    {
        return $this->belongsTo(Pharmacie::class);
    }
}