<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
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

        Password::defaults(fn () 
            => Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
        );

        ResetPassword::createUrlUsing(fn ($user, string $token) 
            => sprintf('%s/reset-password?token=%s&email=%s', 
                config('app.fe_url'),
                $token,
                urlencode($user->email)
            )
        );
    }
}
