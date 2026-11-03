<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Pengguna (semua peran)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Data Master & Transaksi yang hanya bisa dibuat/diubah oleh Admin & Staff (Fase 2 & 3)
    Route::middleware('role:admin|staff')->group(function () {
        Route::get('/products/create', fn () => view('placeholder', ['title' => 'Tambah Barang']))->name('products.create');
        Route::post('/products', fn () => abort(404))->name('products.store');
        Route::get('/products/{product}/edit', fn () => view('placeholder', ['title' => 'Ubah Barang']))->name('products.edit');
        Route::put('/products/{product}', fn () => abort(404))->name('products.update');
        Route::delete('/products/{product}', fn () => abort(404))->name('products.destroy');

        Route::get('/categories', fn () => view('placeholder', ['title' => 'Kategori Barang']))->name('categories.index');
        Route::get('/units', fn () => view('placeholder', ['title' => 'Satuan Barang']))->name('units.index');
        Route::get('/suppliers', fn () => view('placeholder', ['title' => 'Data Supplier']))->name('suppliers.index');

        Route::get('/stock-ins/create', fn () => view('placeholder', ['title' => 'Tambah Barang Masuk']))->name('stock-ins.create');
        Route::post('/stock-ins', fn () => abort(404))->name('stock-ins.store');

        Route::get('/stock-outs/create', fn () => view('placeholder', ['title' => 'Tambah Barang Keluar']))->name('stock-outs.create');
        Route::post('/stock-outs', fn () => abort(404))->name('stock-outs.store');
    });

    // Lihat Barang, Transaksi, dan Kartu Stok (Admin, Staff, Pimpinan — Fase 2 & 3)
    Route::middleware('role:admin|staff|pimpinan')->group(function () {
        Route::get('/products', fn () => view('placeholder', ['title' => 'Data Barang']))->name('products.index');
        Route::get('/products/{product}', fn () => view('placeholder', ['title' => 'Detail Barang']))->name('products.show');

        Route::get('/stock-ins', fn () => view('placeholder', ['title' => 'Barang Masuk']))->name('stock-ins.index');
        Route::get('/stock-ins/{stock_in}', fn () => view('placeholder', ['title' => 'Detail Barang Masuk']))->name('stock-ins.show');

        Route::get('/stock-outs', fn () => view('placeholder', ['title' => 'Barang Keluar']))->name('stock-outs.index');
        Route::get('/stock-outs/{stock_out}', fn () => view('placeholder', ['title' => 'Detail Barang Keluar']))->name('stock-outs.show');

        Route::get('/stock-movements', fn () => view('placeholder', ['title' => 'Kartu Stok']))->name('stock-movements.index');
    });

    // Laporan (Admin & Pimpinan — Fase 5)
    Route::middleware('role:admin|pimpinan')->group(function () {
        Route::get('/reports/{jenis}', fn () => view('placeholder', ['title' => 'Laporan']))->name('reports.show');
        Route::get('/reports/{jenis}/pdf', fn () => abort(404))->name('reports.pdf');
        Route::get('/reports/{jenis}/excel', fn () => abort(404))->name('reports.excel');
    });

    // Kelola Pengguna (Khusus Admin — FR-03)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
