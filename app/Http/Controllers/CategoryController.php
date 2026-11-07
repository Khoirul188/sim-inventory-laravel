<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori barang dengan pencarian dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $categories = $query->orderBy('nama')->paginate($perPage)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Tampilkan form tambah kategori.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Simpan kategori baru.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', "Kategori {$category->nama} berhasil disimpan.");
    }

    /**
     * Tampilkan form ubah kategori.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Perbarui data kategori.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', "Kategori {$category->nama} berhasil diperbarui.");
    }

    /**
     * Hapus kategori jika belum dipakai oleh barang (BR-09, FR-17).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $usedCount = $category->products()->withTrashed()->count();

        if ($usedCount > 0) {
            return back()->with(
                'error',
                "Kategori tidak dapat dihapus karena masih dipakai oleh {$usedCount} barang."
            );
        }

        $nama = $category->nama;
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Kategori {$nama} berhasil dihapus.");
    }
}
