<?php

namespace App\Http\Controllers;

use App\Models\Medecin;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    public function index()
    {
        return Medecin::with('user', 'patients')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'specialite' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        return Medecin::create($validated);
    }

    public function show(Medecin $medecin)
    {
        return $medecin->load('user', 'patients');
    }

    public function update(Request $request, Medecin $medecin)
    {
        $medecin->update($request->all());
        return $medecin;
    }

    public function destroy(Medecin $medecin)
    {
        $medecin->delete();
        return response()->noContent();
    }
}
