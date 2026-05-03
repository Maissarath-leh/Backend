<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        return MedicalRecord::with('patient', 'medecin')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medecin_id' => 'required|exists:medecins,id',
            'blood_type' => 'nullable|string',
            'allergies' => 'nullable|string',
            'antecedents' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'prescriptions' => 'nullable|string',
            'hospitalizations' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return MedicalRecord::create($validated);
    }

    public function show(MedicalRecord $medicalRecord)
    {
        return $medicalRecord->load('patient', 'medecin');
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $medicalRecord->update($request->all());
        return $medicalRecord;
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $medicalRecord->delete();
        return response()->noContent();
    }
}
