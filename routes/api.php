<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\SimulateurController;
use App\Http\Controllers\PasswordResetController;
use App\Models\User;
use App\Models\Medecin;
use App\Models\Pharmacie;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Route::get('/ping', function () {
    return response()->json(['message' => 'API OK']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode']);
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/contact', function (Request $request) {
    $request->validate([
        'nom' => 'required|string',
        'email' => 'required|email',
        'message' => 'required|string',
    ]);
    \App\Models\Contact::create($request->all());
    return response()->json(['message' => 'Message envoyé avec succès.']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pharmacies/liste', function () {
        return \App\Models\Pharmacie::with('user')->get();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('medecins', MedecinController::class);
    Route::apiResource('medical-records', MedicalRecordController::class);

    Route::get('/medecin/mes-patients', function (Request $request) {
        $medecin = $request->user()->medecin;
        if (!$medecin) {
            return response()->json(['message' => 'Profil médecin non trouvé'], 404);
        }
        return Patient::where('medecin_id', $medecin->id)
            ->with(['user', 'mesures' => function ($query) {
                $query->orderBy('date_heure', 'desc')->limit(1);
            }])
            ->get()
            ->map(function ($patient) {
                $derniere = $patient->mesures->first();
                return [
                    'id' => $patient->id,
                    'user_id' => $patient->user_id,
                    'user' => $patient->user,
                    'tension' => $derniere ? $derniere->tension_systolique . '/' . $derniere->tension_diastolique : '—',
                    'frequence_cardiaque' => $derniere->frequence_cardiaque ?? '—',
                    'temperature' => $derniere->temperature ?? '—',
                    'saturation_oxygene' => $derniere->saturation_oxygene ?? '—',
                    'statut' => 'Stable',
                    'alerte' => false,
                ];
            });
    });

    Route::get('/medecin/mes-alertes', function (Request $request) {
        $medecin = $request->user()->medecin;
        if (!$medecin) return response()->json([]);
        $patientIds = Patient::where('medecin_id', $medecin->id)->pluck('id');
        return \App\Models\Alerte::whereIn('patient_id', $patientIds)
            ->where('vue', false)
            ->with('patient.user')
            ->orderBy('created_at', 'desc')
            ->get();
    });

    Route::get('/patient/mes-mesures', function (Request $request) {
        $patient = $request->user()->patient;
        if (!$patient) return response()->json([]);
        return \App\Models\Mesure::where('patient_id', $patient->id)->orderBy('date_heure', 'desc')->get();
    });

    Route::get('/patient/mes-alertes', function (Request $request) {
        $patient = $request->user()->patient;
        if (!$patient) return response()->json([]);
        return \App\Models\Alerte::where('patient_id', $patient->id)->orderBy('created_at', 'desc')->get();
    });

    Route::get('/ordonnances', [OrdonnanceController::class, 'index']);
    Route::post('/ordonnances', [OrdonnanceController::class, 'store']);
    Route::patch('/ordonnances/{ordonnance}/prendre-en-charge', [OrdonnanceController::class, 'prendreEnCharge']);
    Route::patch('/ordonnances/{ordonnance}/valider', [OrdonnanceController::class, 'valider']);

    Route::post('/simulateur/generer', [SimulateurController::class, 'generer']);

    Route::prefix('admin')->group(function () {
        
        Route::get('/stats', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            return response()->json([
                'patients' => User::where('role', 'patient')->count(),
                'medecins' => User::where('role', 'medecin')->count(),
                'pharmacies' => User::where('role', 'pharmacie')->count(),
                'total' => User::count(),
            ]);
        });

        Route::get('/users', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            return response()->json(
                User::select('id', 'nom', 'prenom', 'email', 'telephone', 'role', 'created_at')
                    ->with('patient:id,user_id,medecin_id')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($user) {
                        $user->medecin_id = $user->patient->medecin_id ?? null;
                        return $user;
                    })
            );
        });

        Route::delete('/users/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            $user = User::findOrFail($id);
            if ($user->role === 'admin') {
                return response()->json(['message' => 'Impossible de supprimer un admin'], 403);
            }
            $user->delete();
            return response()->json(['message' => 'Utilisateur supprimé']);
        });

        Route::put('/patients/assigner/{userId}', function (Request $request, $userId) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            $validated = $request->validate([
                'medecin_id' => 'required|exists:medecins,id',
            ]);
            $patient = Patient::where('user_id', $userId)->firstOrFail();
            $patient->medecin_id = $validated['medecin_id'];
            $patient->save();
            return response()->json(['message' => 'Patient assigné avec succès.']);
        });

        Route::post('/medecins', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'telephone' => 'required|string|max:20',
                'password' => 'required|string|min:8',
                'specialite' => 'required|string|max:255',
            ]);

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'nom' => $validated['nom'],
                    'prenom' => $validated['prenom'],
                    'email' => $validated['email'],
                    'telephone' => $validated['telephone'],
                    'password' => Hash::make($validated['password']),
                    'date_naissance' => '1990-01-01',
                    'sexe' => 'Homme',
                    'role' => 'medecin',
                ]);

                Medecin::create([
                    'user_id' => $user->id,
                    'specialite' => $validated['specialite'],
                ]);

                return $user;
            });

            return response()->json(['message' => 'Médecin créé', 'user' => $user], 201);
        });

        Route::post('/pharmacies', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['message' => 'Accès interdit'], 403);
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'telephone' => 'required|string|max:20',
                'password' => 'required|string|min:8',
            ]);

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'nom' => $validated['nom'],
                    'prenom' => $validated['prenom'],
                    'email' => $validated['email'],
                    'telephone' => $validated['telephone'],
                    'password' => Hash::make($validated['password']),
                    'date_naissance' => '1990-01-01',
                    'sexe' => 'Homme',
                    'role' => 'pharmacie',
                ]);

                Pharmacie::create([
                    'user_id' => $user->id,
                    'nom' => $validated['nom'],
                    'adresse' => $validated['telephone'],
                    'telephone' => $validated['telephone'],
                ]);

                return $user;
            });

            return response()->json(['message' => 'Pharmacie créée', 'user' => $user], 201);
        });
    });
});