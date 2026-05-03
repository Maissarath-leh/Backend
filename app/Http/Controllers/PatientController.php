<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        return Patient::with('user', 'medecin')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_naissance' => 'required|date',
            'sexe' => 'required|string',
            'adresse' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'medecin_id' => 'nullable|exists:medecins,id',
        ]);

        return Patient::create($validated);
    }

    public function show(Patient $patient)
    {
        return $patient->load('user', 'medecin');
    }

    public function update(Request $request, Patient $patient)
    {
        $patient->update($request->all());
        return $patient;
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->noContent();
    }
}
