<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public int $lowStockCount = 0;

    public function __construct(
        public string $title = 'Dashboard'
    ) {
        if (Schema::hasTable('products')) {
            $this->lowStockCount = Product::lowStock()->count();
        }
    }

    /**
     * Tampilkan layout utama aplikasi.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
