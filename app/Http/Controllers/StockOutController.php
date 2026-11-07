<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StockOutRequest;
use App\Models\Product;
use App\Models\StockOut;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockOutController extends Controller
{
    /**
     * Tampilkan daftar transaksi barang keluar dengan pencarian, filter alasan & rentang tanggal (FR-25, PRD §7.8).
     */
    public function index(Request $request): View
    {
        $query = StockOut::with('user')
            ->withCount('items')
            ->withSum('items', 'jumlah');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('alasan') && array_key_exists($request->alasan, StockOut::alasanLabels())) {
            $query->where('alasan', $request->alasan);
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

        $stockOuts = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $alasanOptions = StockOut::alasanLabels();

        return view('stock-outs.index', compact('stockOuts', 'alasanOptions'));
    }

    /**
     * Tampilkan form tambah barang keluar dengan baris dinamis dan informasi stok tersedia (FR-22).
     */
    public function create(StockService $stockService): View
    {
        $nextNo = $stockService->generateNumber('BK', now());
        $products = Product::with('unit')->orderBy('nama')->get();
        $alasanOptions = StockOut::alasanLabels();

        return view('stock-outs.create', compact('nextNo', 'products', 'alasanOptions'));
    }

    /**
     * Simpan transaksi barang keluar melalui StockService (FR-22, FR-23, FR-24).
     */
    public function store(StockOutRequest $request, StockService $stockService): RedirectResponse
    {
        try {
            $stockOut = $stockService->createStockOut($request->validated(), $request->user());
        } catch (InsufficientStockException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage())
                ->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('stock-outs.show', $stockOut)
            ->with('success', "Barang keluar {$stockOut->no_transaksi} berhasil disimpan.");
    }

    /**
     * Tampilkan detail transaksi barang keluar (FR-25).
     */
    public function show(StockOut $stockOut): View
    {
        $stockOut->load(['user', 'items.product.unit', 'items.product.category']);

        return view('stock-outs.show', compact('stockOut'));
    }
}

