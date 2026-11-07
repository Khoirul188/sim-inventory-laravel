<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    /**
     * Tampilkan halaman Kartu Stok dengan filter barang dan rentang tanggal (FR-26, PRD §7.8).
     */
    public function index(Request $request): View
    {
        $products = Product::with('unit')->orderBy('nama')->get();

        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = Product::withTrashed()
                ->with(['category', 'unit'])
                ->find($request->integer('product_id'));
        }

        $query = StockMovement::with(['product.unit', 'user', 'stockIn', 'stockOut'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($selectedProduct) {
            $query->where('product_id', $selectedProduct->id);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->input('sampai'));
        }

        if ($request->filled('tipe') && in_array($request->tipe, ['in', 'out'], true)) {
            $query->where('tipe', $request->tipe);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $movements = $query->paginate($perPage)->withQueryString();

        return view('stock-movements.index', compact('products', 'selectedProduct', 'movements'));
    }
}
