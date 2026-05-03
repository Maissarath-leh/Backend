<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pharmacie extends Model
{
    protected $fillable = [
        'user_id',
        'nom',
        'adresse',
        'telephone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}