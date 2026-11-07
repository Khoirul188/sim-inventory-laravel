<?php

namespace App\Providers;

use App\Services\DashboardService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Gunakan resources/views/components/icon.blade.php sebagai pembungkus heroicons outline
        config(['blade-icons.components.default' => null]);

        // Satu instance DashboardService per request agar jumlah stok menipis tidak di-query ulang
        $this->app->scoped(DashboardService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
