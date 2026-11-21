<?php

namespace App\Providers;

use App\Models\Absen;
use App\Models\Siswa;
use App\Observers\SiswaObserver;
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
