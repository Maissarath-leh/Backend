<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Exception;

class ConsultationController extends Controller
{
    /**
     * POST /api/consultations/demander
     * Crée une nouvelle demande de consultation.
     */
    public function demander(Request $request)
    {
        $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'patient_id' => 'required|exists:patients,id',
        ]);

        try {
            $roomName = 'HealthTech-' . uniqid() . '-' . time();

            $consultation = Consultation::create([
                'patient_id' => $request->patient_id,
                'medecin_id' => $request->medecin_id,
                'room_name'  => $roomName,
                'status'     => 'en_attente',
            ]);

            return response()->json([
                'success'      => true,
                'consultation' => $consultation,
                'room_name'    => $roomName,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Impossible de créer la demande de consultation.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/consultations/{id}/accepter
     * Accepte une consultation en attente.
     */
    public function accepter($id)
    {
        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->update(['status' => 'acceptee']);
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/consultations/{id}/rejeter
     * Rejette une consultation en attente.
     */
    public function rejeter($id)
    {
        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->update(['status' => 'rejetee']);
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/consultations/{id}/terminer
     * Termine une consultation en cours.
     */
    public function terminer($id)
    {
        try {
            $consultation = Consultation::findOrFail($id);
            
            // Vérifier que la consultation est soit acceptée, soit en attente
            if ($consultation->status !== 'acceptee' && $consultation->status !== 'en_attente') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Seules les consultations en cours ou en attente peuvent être terminées.'
                ], 400);
            }
            
            $consultation->update(['status' => 'terminee']);
            
            return response()->json([
                'success' => true, 
                'message' => 'Consultation terminée avec succès.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/consultations/medecin/{medecinId}/demandes
     * Récupère les demandes en attente pour un médecin spécifique.
     */
    public function demandesEnAttente($medecinId)
    {
        try {
            $consultations = Consultation::with(['patient.user'])
                ->where('medecin_id', $medecinId)
                ->where('status', 'en_attente')
                ->get();
                
            return response()->json($consultations);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/consultations/patient/{patientId}/actives
     * Récupère les consultations actives d'un patient.
     */
    public function consultationsActives($patientId)
    {
        try {
            $consultations = Consultation::with(['medecin.user'])
                ->where('patient_id', $patientId)
                ->get();

            return response()->json($consultations);
        } catch (Exception $e) {
            return response()->json([
                'error'   => 'Erreur lors de la récupération des consultations.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}