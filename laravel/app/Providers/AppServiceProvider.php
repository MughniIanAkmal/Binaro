<?php

namespace App\Providers;

use App\Features\Models\Absen;
use App\Features\Models\Siswa;
use App\Features\Observers\SiswaObserver;
use App\Policies\AbsenPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Siswa::observe(SiswaObserver::class);

        // Daftarkan Gate untuk model Absen.
        Gate::policy(Absen::class, AbsenPolicy::class);
    }
}
