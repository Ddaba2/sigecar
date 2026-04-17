<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Fournisseur de services d'événements
 * Définit les écouteurs d'événements et la découverte automatique
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Événements et leurs écouteurs
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Démarre les services d'événements
     */
    public function boot(): void
    {
        //
    }

    /**
     * Détermine si les événements doivent être découverts automatiquement
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
