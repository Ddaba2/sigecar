<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Log;

/**
 * Contrôleur pour la gestion de l'authentification
 */
class AuthController extends Controller
{
    /**
     * Affiche la page de connexion
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Traite la connexion de l'utilisateur
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Détermination du champ pour l'authentification (email ou nom)
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Tentative d'authentification
        if (Auth::attempt([$field => $request->username, 'password' => $request->password], $request->remember)) {
            $user = Auth::user();

            // Vérifier si l'utilisateur est actif
            if ($user->status !== 'active') {
                Auth::logout();
                return redirect('/login')->withErrors([
                    'username' => 'Votre compte est désactivé.',
                ]);
            }

            $request->session()->regenerate();

            // Log de connexion
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'Connexion au système',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            /** @var User $user */
            // Redirection selon le rôle
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isGestionnaire()) {
                return redirect()->route('gestionnaire.dashboard');
            } elseif ($user->isMarketeur()) {
                return redirect()->route('marketeur.dashboard');
            }

            return redirect('/dashboard');
        }

        // Échec de l'authentification
        return redirect('/login')->withErrors([
            'username' => 'Identifiants incorrects.',
        ]);
    }

    /**
     * Traite la déconnexion de l'utilisateur
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Log de déconnexion si l'utilisateur est connecté
        if (Auth::check()) {
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'description' => 'Déconnexion du système',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Déconnexion et redirection
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
