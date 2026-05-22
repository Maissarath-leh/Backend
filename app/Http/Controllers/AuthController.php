<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'telephone' => 'required|string|max:20',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:Homme,Femme',
            'adresse' => 'nullable|string',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'],
                'password' => $validated['password'],
                'date_naissance' => $validated['date_naissance'],
                'sexe' => $validated['sexe'],
                'adresse' => $validated['adresse'] ?? null,
                'role' => 'patient',
            ]);

            Patient::create([
                'user_id' => $user->id,
                'medecin_id' => null,
            ]);

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('patient'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load(['patient', 'medecin', 'pharmacie']);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->makeHidden(['password', 'remember_token']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Lien de réinitialisation envoyé à votre adresse e-mail.'])
            : response()->json(['message' => "Impossible d'envoyer le lien de réinitialisation."], 500);
    }

    public function user(Request $request)
    {
        return response()->json($request->user()->load(['patient', 'medecin', 'pharmacie']));
    }
}