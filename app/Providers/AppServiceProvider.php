<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Marketeur;

/**
 * Fournisseur de services principal de l'application
 * Configure les services globaux et les vues partagées
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services dans le conteneur
     */
    public function register(): void
    {
        //
    }

    /**
     * Démarre les services après l'enregistrement
     */
    public function boot(): void
    {
        // Partage les marketeurs actifs avec certaines vues
        View::composer(['gestionnaire.cession-create', 'gestionnaire.depotage-create'], function ($view) {
            $view->with('marketeurs', Marketeur::where('status', 'active')->get());
        });
    }
}
