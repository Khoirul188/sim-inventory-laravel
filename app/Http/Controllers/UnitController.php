<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Tampilkan daftar satuan barang dengan pencarian dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Unit::withCount('products');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $units = $query->orderBy('nama')->paginate($perPage)->withQueryString();

        return view('units.index', compact('units'));
    }

    /**
     * Tampilkan form tambah satuan.
     */
    public function create(): View
    {
        return view('units.create');
    }

    /**
     * Simpan satuan baru.
     */
    public function store(UnitRequest $request): RedirectResponse
    {
        $unit = Unit::create($request->validated());

        return redirect()->route('units.index')
            ->with('success', "Satuan {$unit->nama} berhasil disimpan.");
    }

    /**
     * Tampilkan form ubah satuan.
     */
    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    /**
     * Perbarui data satuan.
     */
    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('units.index')
            ->with('success', "Satuan {$unit->nama} berhasil diperbarui.");
    }

    /**
     * Hapus satuan jika belum dipakai oleh barang (BR-09, FR-17).
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $usedCount = $unit->products()->withTrashed()->count();

        if ($usedCount > 0) {
            return back()->with(
                'error',
                "Satuan tidak dapat dihapus karena masih dipakai oleh {$usedCount} barang."
            );
        }

        $nama = $unit->nama;
        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', "Satuan {$nama} berhasil dihapus.");
    }
}
