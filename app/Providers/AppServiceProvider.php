<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Absensi;
use App\Observers\AbsensiObserver;

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
        // Register observer untuk auto-clear cache widget saat ada perubahan data absensi
        Absensi::observe(AbsensiObserver::class);
    }
}
