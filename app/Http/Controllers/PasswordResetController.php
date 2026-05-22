<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class PasswordResetController extends Controller
{
    public function sendCode(Request $request)
    {
        // 1. On trim l'email pour virer les espaces avant/après
        $request->merge(['email' => trim(strtolower($request->email))]);
        
        $request->validate([
            'email' => 'required|email',
        ]);

        // 2. On vérifie manuellement au lieu de `exists:users,email` qui bug parfois
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json(['message' => 'Email introuvable.'], 404);
        }

        $code = rand(100000, 999999);

        Cache::put('reset_code_' . $request->email, $code, now()->addMinutes(10));

        Mail::raw("Votre code de réinitialisation HealthTech est : $code\n\nCe code expire dans 10 minutes.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Code de réinitialisation HealthTech');
        });

        return response()->json(['message' => 'Code envoyé par email.']);
    }

    public function verifyCode(Request $request)
    {
        $request->merge(['email' => trim(strtolower($request->email))]);
        
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required',
        ]);

        $cached = Cache::get('reset_code_' . $request->email);

        if (!$cached || $cached != $request->code) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        return response()->json(['message' => 'Code valide.']);
    }

    public function resetPassword(Request $request)
    {
        $request->merge(['email' => trim(strtolower($request->email))]);
        
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $cached = Cache::get('reset_code_' . $request->email);

        if (!$cached || $cached != $request->code) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json(['message' => 'Email introuvable.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        Cache::forget('reset_code_' . $request->email);

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}