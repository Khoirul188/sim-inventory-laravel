<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier dengan pencarian dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Supplier::withCount('stockIns');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $suppliers = $query->orderBy('nama')->paginate($perPage)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Tampilkan form tambah supplier.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Simpan supplier baru.
     */
    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier {$supplier->nama} berhasil disimpan.");
    }

    /**
     * Tampilkan form ubah supplier.
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Perbarui data supplier.
     */
    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier {$supplier->nama} berhasil diperbarui.");
    }

    /**
     * Hapus supplier jika belum memiliki transaksi barang masuk (BR-09, FR-17).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $usedCount = $supplier->stockIns()->count();

        if ($usedCount > 0) {
            return back()->with(
                'error',
                "Supplier tidak dapat dihapus karena masih dipakai oleh {$usedCount} barang."
            );
        }

        $nama = $supplier->nama;
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier {$nama} berhasil dihapus.");
    }
}
