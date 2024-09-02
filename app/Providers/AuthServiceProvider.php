<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Passport::loadKeysFrom(_DIR_.'/../secrets/oauth');
        /*  passport::hashClientSecrets();
         Passport::tokensExpireIn(now()->second(60));
         Passport::refreshTokensExpireIn(now()->addDays(30)); 
         Passport::personalAccessTokensExpireIn(now()->addMonths(6)); */
        /* $this->registerPolicies(); */
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addMinutes(5)); // Adjust as needed
        Passport::refreshTokensExpireIn(now()->addDays(1)); // Adjust as needed
        Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Adjust 
    }
}
