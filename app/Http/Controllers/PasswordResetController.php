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
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

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
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'code'     => 'required',
            'password' => 'required|min:6',
        ]);

        $cached = Cache::get('reset_code_' . $request->email);

        if (!$cached || $cached != $request->code) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        Cache::forget('reset_code_' . $request->email);

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}