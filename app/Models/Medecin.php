<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    // Colonnes que l’on peut remplir en masse
    protected $fillable = [
        'specialite',
        'user_id',
    ];

    /**
     * Relation avec le modèle User
     * Chaque médecin est lié à un utilisateur (nom, prénom, email, téléphone, rôle).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les patients
     * Un médecin peut suivre plusieurs patients.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Relation avec les dossiers médicaux
     * Un médecin peut créer plusieurs dossiers médicaux.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
