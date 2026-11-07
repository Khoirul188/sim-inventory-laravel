<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar barang dengan pencarian, filter kategori/status, pengurutan, dan paginasi (FR-14).
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'unit']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'aman' => $query->whereColumn('stok', '>', 'stok_minimum')->where('stok', '>', 0),
                'menipis' => $query->lowStock(),
                'habis' => $query->outOfStock(),
                default => null,
            };
        }

        // Pengurutan bawaan: nama A–Z (PRD §7.8)
        match ($request->input('sort')) {
            'kode' => $query->orderBy('kode_barang'),
            'stok_asc' => $query->orderBy('stok'),
            'stok_desc' => $query->orderByDesc('stok'),
            'terbaru' => $query->orderByDesc('created_at'),
            default => $query->orderBy('nama'),
        };

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $products = $query->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('nama')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Tampilkan form tambah barang baru.
     */
    public function create(): View
    {
        $categories = Category::orderBy('nama')->get();
        $units = Unit::orderBy('nama')->get();
        $nextKode = Product::generateKodeBarang();

        return view('products.create', compact('categories', 'units', 'nextKode'));
    }

    /**
     * Simpan barang baru dengan stok awal 0 (BR-13) dan kode otomatis bila kosong (FR-15).
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['kode_barang'])) {
            $validated['kode_barang'] = Product::generateKodeBarang();
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('products', 'public');
        }

        // Stok awal selalu 0; perubahan stok hanya lewat StockService (BR-04, BR-13)
        $validated['stok'] = 0;

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', "Barang {$product->nama} berhasil disimpan.");
    }

    /**
     * Tampilkan detail barang beserta tabel kartu stoknya.
     */
    public function show(Request $request, Product $product): View
    {
        $product->load(['category', 'unit']);

        $movementsQuery = $product->stockMovements()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('dari')) {
            $movementsQuery->whereDate('created_at', '>=', $request->input('dari'));
        }

        if ($request->filled('sampai')) {
            $movementsQuery->whereDate('created_at', '<=', $request->input('sampai'));
        }

        $movements = $movementsQuery->paginate(10)->withQueryString();

        return view('products.show', compact('product', 'movements'));
    }

    /**
     * Tampilkan form ubah barang.
     */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('nama')->get();
        $units = Unit::orderBy('nama')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Perbarui data barang tanpa mengubah stok (BR-04).
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['kode_barang'])) {
            $validated['kode_barang'] = $product->kode_barang ?: Product::generateKodeBarang();
        }

        if ($request->hasFile('foto')) {
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }
            $validated['foto'] = $request->file('foto')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', "Barang {$product->nama} berhasil diperbarui.");
    }

    /**
     * Soft delete barang agar riwayat transaksi dan laporan historis tetap terjaga (FR-16, BR-10).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $nama = $product->nama;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Barang {$nama} berhasil dihapus.");
    }
}
