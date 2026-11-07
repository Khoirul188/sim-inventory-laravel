<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Pengguna (semua peran — tanpa hapus akun sendiri sesuai BR-12 & PRD §5.3)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Data Master & Transaksi yang hanya bisa dibuat/diubah/dihapus oleh Admin & Staff
    Route::middleware('role:admin|staff')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('units', UnitController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);

        // Transaksi Tambah/Simpan (Admin & Staff — tanpa edit/update/destroy sesuai BR-06)
        Route::get('/stock-ins/create', [StockInController::class, 'create'])->name('stock-ins.create');
        Route::post('/stock-ins', [StockInController::class, 'store'])->name('stock-ins.store');

        Route::get('/stock-outs/create', [StockOutController::class, 'create'])->name('stock-outs.create');
        Route::post('/stock-outs', [StockOutController::class, 'store'])->name('stock-outs.store');
    });

    // Lihat Barang, Daftar/Detail Transaksi, dan Kartu Stok (Admin, Staff, Pimpinan — PRD §5.2 & §16)
    Route::middleware('role:admin|staff|pimpinan')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/stock-ins', [StockInController::class, 'index'])->name('stock-ins.index');
        Route::get('/stock-ins/{stock_in}', [StockInController::class, 'show'])->name('stock-ins.show');

        Route::get('/stock-outs', [StockOutController::class, 'index'])->name('stock-outs.index');
        Route::get('/stock-outs/{stock_out}', [StockOutController::class, 'show'])->name('stock-outs.show');

        Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    });

    // Laporan (Admin & Pimpinan — Fase 5)
    Route::middleware('role:admin|pimpinan')->group(function () {
        Route::get('/reports/{jenis}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{jenis}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('/reports/{jenis}/excel', [ReportController::class, 'excel'])->name('reports.excel');
    });

    // Kelola Pengguna (Khusus Admin — FR-03)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
