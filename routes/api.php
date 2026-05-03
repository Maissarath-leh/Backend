<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrdonnanceController;

Route::get('/ping', function () {
    return response()->json(['message' => 'API OK']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('medecins', MedecinController::class);
    Route::apiResource('medical-records', MedicalRecordController::class);

    Route::get('/ordonnances', [OrdonnanceController::class, 'index']);
    Route::post('/ordonnances', [OrdonnanceController::class, 'store']);
    Route::get('/ordonnances/en-attente', [OrdonnanceController::class, 'enAttente']);
    Route::get('/ordonnances/stats', [OrdonnanceController::class, 'stats']);
    Route::get('/ordonnances/patient/{id}', [OrdonnanceController::class, 'parPatient']);
    Route::put('/ordonnances/{id}/statut', [OrdonnanceController::class, 'updateStatut']);

    Route::get('/admin/stats', function () {
        return response()->json([
            'patients'   => \App\Models\User::where('role', 'patient')->count(),
            'medecins'   => \App\Models\User::where('role', 'medecin')->count(),
            'pharmacies' => \App\Models\User::where('role', 'pharmacie')->count(),
            'total'      => \App\Models\User::count(),
        ]);
    });

    Route::get('/admin/users', function () {
        return response()->json(\App\Models\User::all());
    });

    Route::delete('/admin/users/{id}', function ($id) {
        \App\Models\User::findOrFail($id)->delete();
        return response()->json(['message' => 'Utilisateur supprimé.']);
    });
});