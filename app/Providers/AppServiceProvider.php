<?php

namespace App\Providers;

use App\Observers\SiswaObserver;
use App\Models\Siswa;
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
    }
}
