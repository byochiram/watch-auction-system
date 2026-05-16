<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use App\Livewire\Profile\UpdateProfileInformationForm;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alias bawaan Jetstream: 'profile.update-profile-information-form'
        Livewire::component(
            'profile.update-profile-information-form',
            UpdateProfileInformationForm::class
        );

        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()   // harus ada huruf
                ->numbers();  // harus ada angka

            // Kalau di production, sedikit lebih ketat:
            if (app()->isProduction()) {
                $rule->mixedCase()    // ada huruf besar & kecil
                    ->symbols()      // ada simbol
                    ->uncompromised(); // tidak termasuk password yang pernah bocor publik
            }

            return $rule;
        });

        if (config('app.env') !== 'local' || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            URL::forceScheme('https');
        }
    }
}
