<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Handle a registration attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur lors de la création de l'utilisateur
            Log::error('User creation failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'User creation failed']);
        }

        if (!$user) {
            // En cas d'échec de la création de l'utilisateur
            Log::error('User creation failed');
            return redirect()->back()->withErrors(['error' => 'User creation failed']);
        }

        Log::info('User created successfully: ' . $user->email);

        // Envoyer la notification de vérification d'email
        $user->sendEmailVerificationNotification();

        Log::info('Email verification notification sent');

        // Connexion automatique de l'utilisateur après l'inscription
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}
