<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard (kerangka sementara Fase 1 dengan angka dari database).
     */
    public function index(): View
    {
        $totalProducts = Product::count();
        $totalStock = (int) Product::sum('stok');
        $monthlyIn = (int) StockMovement::where('tipe', 'in')
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('jumlah');
        $monthlyOut = (int) StockMovement::where('tipe', 'out')
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('jumlah');
        $lowStockCount = Product::lowStock()->count();

        return view('dashboard', compact(
            'totalProducts',
            'totalStock',
            'monthlyIn',
            'monthlyOut',
            'lowStockCount'
        ));
    }
}
