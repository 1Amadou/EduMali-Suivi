<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Décommenter si vous utilisez Gates/Policies
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Exemple
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // $this->registerPolicies(); // Décommenter si vous utilisez Gates/Policies

        //
    }
}