<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // Register any model-policy mappings here
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        if (! app()->environment('production')) {
            $this->app->make(\Laravel\Passport\Passport::class)->loadKeysFrom(__DIR__.'/../secrets/oauth');
        }
        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        Gate::define('view-admin-dashboard', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('view-manager-dashboard', function (User $user) {
            return $user->role === 'manager';
        });

        Gate::define('view-employee-dashboard', function (User $user) {
            return $user->role === 'employee';
        });
    }
}
