<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    // Colonnes que l’on peut remplir en masse (mass assignment)
    protected $fillable = [
        'date_naissance',
        'sexe',
        'adresse',
        'user_id',
        'medecin_id',
    ];

    /**
     * Relation avec le modèle User
     * Chaque patient est lié à un utilisateur (nom, prénom, email, téléphone, rôle).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le modèle Medecin
     * Un patient peut être suivi par un médecin.
     */
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    /**
     * Relation avec les dossiers médicaux
     * Un patient peut avoir plusieurs dossiers médicaux.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
