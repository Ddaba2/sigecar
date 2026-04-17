<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Fournisseur de services d'authentification
 * Définit les politiques d'autorisation et les gates
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Politiques d'autorisation
     */
    protected $policies = [
        //
    ];

    /**
     * Démarre les services d'authentification
     */
    public function boot(): void
    {
        //
    }
}
