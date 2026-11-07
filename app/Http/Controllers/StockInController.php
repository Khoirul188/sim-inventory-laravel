<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
    /**
     * Tampilkan daftar transaksi barang masuk dengan pencarian, filter supplier & rentang tanggal (FR-21, PRD §7.8).
     */
    public function index(Request $request): View
    {
        $query = StockIn::with(['supplier', 'user'])
            ->withCount('items')
            ->withSum('items', 'jumlah');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->input('sampai'));
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $stockIns = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama')->get();

        return view('stock-ins.index', compact('stockIns', 'suppliers'));
    }

    /**
     * Tampilkan form tambah barang masuk dengan baris dinamis (FR-18).
     */
    public function create(StockService $stockService): View
    {
        $nextNo = $stockService->generateNumber('BM', now());
        $suppliers = Supplier::orderBy('nama')->get();
        $products = Product::with('unit')->orderBy('nama')->get();

        return view('stock-ins.create', compact('nextNo', 'suppliers', 'products'));
    }

    /**
     * Simpan transaksi barang masuk melalui StockService (FR-18, FR-19, FR-20).
     */
    public function store(StockInRequest $request, StockService $stockService): RedirectResponse
    {
        $stockIn = $stockService->createStockIn($request->validated(), $request->user());

        return redirect()->route('stock-ins.show', $stockIn)
            ->with('success', "Barang masuk {$stockIn->no_transaksi} berhasil disimpan.");
    }

    /**
     * Tampilkan detail transaksi barang masuk (FR-21).
     */
    public function show(StockIn $stockIn): View
    {
        $stockIn->load(['supplier', 'user', 'items.product.unit', 'items.product.category']);

        return view('stock-ins.show', compact('stockIn'));
    }
}

