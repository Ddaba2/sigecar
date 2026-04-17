<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware pour vérifier les rôles des utilisateurs
 */
class RoleMiddleware
{
    /**
     * Traite la requête entrante
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Vérification de l'authentification
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        /** @var \App\Models\User $user */
        // Vérification du rôle
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Redirection selon le rôle de l'utilisateur
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isGestionnaire()) {
            return redirect()->route('gestionnaire.dashboard');
        } elseif ($user->isMarketeur()) {
            return redirect()->route('marketeur.dashboard');
        }

        // Accès refusé
        abort(403, 'Accès non autorisé.');
    }
}
