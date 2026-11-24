<?php

namespace App\Providers;

use App\Features\Models\Absen;
use App\Features\Models\Siswa;
use App\Features\Observers\SiswaObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::policy(Absen::class, \App\Policies\AbsenPolicy::class);
    }
}
