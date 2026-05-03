<?php

namespace App\Http\Controllers;

use App\Models\Ordonnance;
use App\Models\User;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    // Liste de toutes les ordonnances
    public function index()
    {
        $ordonnances = Ordonnance::with(['patient.user', 'medecin.user'])->get();
        return response()->json($ordonnances);
    }

    // Ordonnances d'un patient
    public function parPatient($patientId)
    {
        $ordonnances = Ordonnance::with(['medecin.user'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($ordonnances);
    }

    // Ordonnances en attente (pour la pharmacie)
    public function enAttente()
    {
        $ordonnances = Ordonnance::with(['patient.user', 'medecin.user'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($ordonnances);
    }

    // Créer une ordonnance (médecin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'medecin_id'       => 'required|exists:medecins,id',
            'medicaments'      => 'required|string',
            'instructions'     => 'nullable|string',
            'date_prescription'=> 'required|date',
            'date_expiration'  => 'nullable|date',
        ]);

        $ordonnance = Ordonnance::create($validated);
        return response()->json($ordonnance, 201);
    }

    // Valider ou refuser une ordonnance (pharmacie)
    public function updateStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:validee,refusee',
        ]);

        $ordonnance = Ordonnance::findOrFail($id);
        $ordonnance->update(['statut' => $request->statut]);
        return response()->json($ordonnance);
    }

    // Statistiques pour admin
    public function stats()
    {
        return response()->json([
            'total'      => Ordonnance::count(),
            'en_attente' => Ordonnance::where('statut', 'en_attente')->count(),
            'validees'   => Ordonnance::where('statut', 'validee')->count(),
            'refusees'   => Ordonnance::where('statut', 'refusee')->count(),
        ]);
    }
}