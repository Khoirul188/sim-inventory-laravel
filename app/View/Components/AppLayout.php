<?php

namespace App\View\Components;

use App\Services\DashboardService;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public int $lowStockCount = 0;

    public function __construct(
        public string $title = 'Dashboard',
        ?int $lowStockCount = null,
    ) {
        $this->lowStockCount = $lowStockCount ?? app(DashboardService::class)->getLowStockCount();
    }

    /**
     * Tampilkan layout utama aplikasi.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
