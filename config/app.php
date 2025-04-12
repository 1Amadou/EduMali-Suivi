<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    | Nom utilisé dans les notifications, UI, etc.
    */
    'name' => env('APP_NAME', 'EduMaliApp'), // Vous pouvez personnaliser 'EduMaliApp'

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    | Détermine l'environnement ('local', 'production', 'testing', etc.).
    | Configuré via le fichier .env (APP_ENV).
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    | Si true, affiche les erreurs détaillées. Désactiver en production !
    | Configuré via le fichier .env (APP_DEBUG).
    */
    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    | URL de base utilisée par Artisan pour générer des URLs.
    | Configuré via le fichier .env (APP_URL).
    */
    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    | Fuseau horaire par défaut pour les fonctions date/heure PHP.
    */
    'timezone' => env('APP_TIMEZONE', 'Africa/Bamako'), // Mis à jour pour Bamako

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    | Locale par défaut pour la traduction et la localisation.
    */
    'locale' => env('APP_LOCALE', 'fr'), // Mis à 'fr' comme locale par défaut

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'fr_FR'), // Faker en français si besoin

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    | Clé utilisée pour le chiffrement. Doit être définie via `php artisan key:generate`.
    */
    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    */
    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis', // Optionnel si vous utilisez Redis pour le mode maintenance
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    | Ces Service Providers sont chargés au démarrage de l'application.
    | **C'EST LA SECTION IMPORTANTE POUR LES ROUTES.**
    */
    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class, // Décommenter si vous utilisez le broadcasting
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class, // <-- Assurez-vous que cette ligne est présente et NON commentée

    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    | Ce tableau d'alias de classes sera enregistré au démarrage.
    */
    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class, // Exemple d'alias
    ])->toArray(),

];