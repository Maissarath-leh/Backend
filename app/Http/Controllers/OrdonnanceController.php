<?php

namespace App\Http\Controllers;

use App\Models\Ordonnance;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'medecin') {
            return Ordonnance::where('medecin_id', $user->medecin->id)
                ->with(['patient.user', 'pharmacie.user'])
                ->latest()
                ->get();
        }

        if ($user->role === 'pharmacie') {
            return Ordonnance::where('pharmacie_id', $user->pharmacie->id)
                ->orWhere('statut', 'en_attente')
                ->with(['patient.user', 'medecin.user'])
                ->latest()
                ->get();
        }

        if ($user->role === 'patient') {
            return Ordonnance::where('patient_id', $user->patient->id)
                ->with(['medecin.user', 'pharmacie.user'])
                ->latest()
                ->get();
        }

        return Ordonnance::with(['patient.user', 'medecin.user', 'pharmacie.user'])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $medecin = $request->user()->medecin;

        if (!$medecin) {
            return response()->json(['message' => 'Profil médecin non trouvé.'], 404);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'contenu' => 'required|array|min:1',
            'contenu.*.medicament' => 'required|string|max:255',
            'contenu.*.posologie' => 'required|string|max:255',
            'contenu.*.duree' => 'required|string|max:100',
        ]);

        $ordonnance = Ordonnance::create([
            'medecin_id' => $medecin->id,
            'patient_id' => $validated['patient_id'],
            'contenu' => $validated['contenu'],
            'statut' => 'en_attente',
        ]);

        return response()->json($ordonnance->load(['patient.user', 'medecin.user']), 201);
    }

    public function prendreEnCharge(Request $request, Ordonnance $ordonnance)
    {
        $pharmacie = $request->user()->pharmacie;

        if (!$pharmacie) {
            return response()->json(['message' => 'Profil pharmacie non trouvé.'], 404);
        }

        if ($ordonnance->statut !== 'en_attente') {
            return response()->json(['message' => 'Ordonnance non disponible'], 422);
        }

        $ordonnance->update([
            'pharmacie_id' => $pharmacie->id,
            'statut' => 'prise_en_charge',
        ]);

        return response()->json($ordonnance->load(['patient.user', 'medecin.user', 'pharmacie.user']));
    }

    public function valider(Request $request, Ordonnance $ordonnance)
    {
        $pharmacie = $request->user()->pharmacie;

        if (!$pharmacie) {
            return response()->json(['message' => 'Profil pharmacie non trouvé.'], 404);
        }

        if ($ordonnance->pharmacie_id !== $pharmacie->id || $ordonnance->statut !== 'prise_en_charge') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:validee,refusee',
            'motif_refus' => 'required_if:statut,refusee|nullable|string|max:500',
        ]);

        $ordonnance->update([
            'statut' => $validated['statut'],
            'motif_refus' => $validated['motif_refus'] ?? null,
        ]);

        return response()->json($ordonnance->load(['patient.user', 'medecin.user', 'pharmacie.user']));
    }
}