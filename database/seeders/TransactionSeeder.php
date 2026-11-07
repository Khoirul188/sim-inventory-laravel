<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Buat riwayat transaksi barang masuk dan keluar 9 bulan terakhir melalui StockService (AGENTS.md §6.4, PRD §24).
     * Seluruh perubahan stok hanya lewat StockService agar kartu stok 100% konsisten dengan kolom products.stok.
     */
    public function run(): void
    {
        if (StockIn::exists() || StockOut::exists()) {
            return;
        }

        $stockService = app(StockService::class);
        $staff = User::where('email', 'staff@inventory.test')->first() ?? User::firstOrFail();
        $admin = User::where('email', 'admin@inventory.test')->first() ?? $staff;
        $suppliers = Supplier::orderBy('id')->pluck('id')->values();
        $products = Product::orderBy('kode_barang')->get()->keyBy('kode_barang');

        if ($products->count() < 32 || $suppliers->isEmpty()) {
            return;
        }

        $sup = fn (int $idx) => $suppliers[$idx % $suppliers->count()];
        $pid = fn (string $kode) => $products[$kode]->id;
        $harga = fn (string $kode) => (float) $products[$kode]->harga_beli;

        // 1. Bulan ke-8 lalu: Stok awal gudang (seluruh 32 barang masuk pertama kali)
        $d8In1 = now()->subMonths(8)->startOfMonth()->addDays(2);
        $stockService->createStockIn([
            'tanggal' => $d8In1->toDateString(),
            'created_at' => $d8In1->copy()->setTime(8, 30),
            'supplier_id' => $sup(0),
            'keterangan' => 'Stok awal gudang kelompok Kertas & ATK',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 50, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 45, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 30, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 25, 'harga_beli' => $harga('BRG-0004')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 35, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 30, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 25, 'harga_beli' => $harga('BRG-0007')],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0008')],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 30, 'harga_beli' => $harga('BRG-0009')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 28, 'harga_beli' => $harga('BRG-0010')],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0011')],
                ['product_id' => $pid('BRG-0012'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0012')],
                ['product_id' => $pid('BRG-0013'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0013')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 45, 'harga_beli' => $harga('BRG-0014')],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 35, 'harga_beli' => $harga('BRG-0015')],
            ],
        ], $admin);

        $d8In2 = now()->subMonths(8)->startOfMonth()->addDays(4);
        $stockService->createStockIn([
            'tanggal' => $d8In2->toDateString(),
            'created_at' => $d8In2->copy()->setTime(9, 15),
            'supplier_id' => $sup(1),
            'keterangan' => 'Stok awal gudang kelompok Printer, Elektronik, & Perlengkapan',
            'items' => [
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0016')],
                ['product_id' => $pid('BRG-0017'), 'jumlah' => 16, 'harga_beli' => $harga('BRG-0017')],
                ['product_id' => $pid('BRG-0018'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0018')],
                ['product_id' => $pid('BRG-0019'), 'jumlah' => 16, 'harga_beli' => $harga('BRG-0019')],
                ['product_id' => $pid('BRG-0020'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0020')],
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0021')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 22, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0023'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0023')],
                ['product_id' => $pid('BRG-0024'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0024')],
                ['product_id' => $pid('BRG-0025'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0025')],
                ['product_id' => $pid('BRG-0026'), 'jumlah' => 22, 'harga_beli' => $harga('BRG-0026')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 28, 'harga_beli' => $harga('BRG-0027')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 50, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0029'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0029')],
                ['product_id' => $pid('BRG-0030'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0030')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 40, 'harga_beli' => $harga('BRG-0031')],
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 22, 'harga_beli' => $harga('BRG-0032')],
            ],
        ], $admin);

        $d8Out = now()->subMonths(8)->startOfMonth()->addDays(18);
        $stockService->createStockOut([
            'tanggal' => $d8Out->toDateString(),
            'created_at' => $d8Out->copy()->setTime(14, 0),
            'tujuan' => 'Divisi Administrasi & Keuangan',
            'alasan' => 'pemakaian',
            'keterangan' => 'Distribusi kebutuhan operasional awal bulan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 8],
            ],
        ], $staff);

        // 2. Bulan ke-7 lalu
        $d7In = now()->subMonths(7)->startOfMonth()->addDays(5);
        $stockService->createStockIn([
            'tanggal' => $d7In->toDateString(),
            'created_at' => $d7In->copy()->setTime(10, 0),
            'supplier_id' => $sup(2),
            'keterangan' => 'Restock rutin kertas dan alat tulis',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0014')],
            ],
        ], $staff);

        $d7Out = now()->subMonths(7)->startOfMonth()->addDays(19);
        $stockService->createStockOut([
            'tanggal' => $d7Out->toDateString(),
            'created_at' => $d7Out->copy()->setTime(15, 10),
            'tujuan' => 'Bagian SDM & Umum',
            'alasan' => 'pemakaian',
            'keterangan' => 'Kebutuhan pelatihan karyawan baru',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 15],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 10], // BRG-0008 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 6],
            ],
        ], $staff);

        // 3. Bulan ke-6 lalu
        $d6In = now()->subMonths(6)->startOfMonth()->addDays(7);
        $stockService->createStockIn([
            'tanggal' => $d6In->toDateString(),
            'created_at' => $d6In->copy()->setTime(9, 45),
            'supplier_id' => $sup(3),
            'keterangan' => 'Pengadaan tinta printer dan perlengkapan IT',
            'items' => [
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0016')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0031')],
            ],
        ], $staff);

        $d6Out = now()->subMonths(6)->startOfMonth()->addDays(21);
        $stockService->createStockOut([
            'tanggal' => $d6Out->toDateString(),
            'created_at' => $d6Out->copy()->setTime(13, 30),
            'tujuan' => 'Divisi Operasional & Gudang',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pemakaian perlengkapan cetak dan pengemasan',
            'items' => [
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0018'), 'jumlah' => 12], // BRG-0018 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 15],
            ],
        ], $staff);

        // 4. Bulan ke-5 lalu
        $d5In = now()->subMonths(5)->startOfMonth()->addDays(6);
        $stockService->createStockIn([
            'tanggal' => $d5In->toDateString(),
            'created_at' => $d5In->copy()->setTime(10, 20),
            'supplier_id' => $sup(0),
            'keterangan' => 'Penambahan stok ordner dan amplop',
            'items' => [
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0004')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0005')],
            ],
        ], $staff);

        $d5Out = now()->subMonths(5)->startOfMonth()->addDays(20);
        $stockService->createStockOut([
            'tanggal' => $d5Out->toDateString(),
            'created_at' => $d5Out->copy()->setTime(16, 0),
            'tujuan' => 'Bagian Sekretariat',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pengarsipan dokumen semester',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0024'), 'jumlah' => 18], // BRG-0024 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0026'), 'jumlah' => 14], // BRG-0026 tersisa 8 (menipis, min 10)
            ],
        ], $staff);

        // 5. Bulan ke-4 lalu
        $d4In = now()->subMonths(4)->startOfMonth()->addDays(8);
        $stockService->createStockIn([
            'tanggal' => $d4In->toDateString(),
            'created_at' => $d4In->copy()->setTime(9, 10),
            'supplier_id' => $sup(1),
            'keterangan' => 'Pengadaan bulanan perlengkapan kebersihan',
            'items' => [
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0032')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0027')],
            ],
        ], $staff);

        $d4Out = now()->subMonths(4)->startOfMonth()->addDays(22);
        $stockService->createStockOut([
            'tanggal' => $d4Out->toDateString(),
            'created_at' => $d4Out->copy()->setTime(14, 45),
            'tujuan' => 'Divisi IT & Layanan Teknis',
            'alasan' => 'pemakaian',
            'keterangan' => 'Penggantian perangkat mouse dan binder clip',
            'items' => [
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 10], // BRG-0021 tersisa 4 (menipis, min 5)
                ['product_id' => $pid('BRG-0030'), 'jumlah' => 20], // BRG-0030 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 20],
            ],
        ], $staff);

        // 6. Bulan ke-3 lalu
        $d3In = now()->subMonths(3)->startOfMonth()->addDays(5);
        $stockService->createStockIn([
            'tanggal' => $d3In->toDateString(),
            'created_at' => $d3In->copy()->setTime(11, 0),
            'supplier_id' => $sup(2),
            'keterangan' => 'Penerimaan kertas F4 dan spidol',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0011')],
            ],
        ], $staff);

        $d3Out = now()->subMonths(3)->startOfMonth()->addDays(17);
        $stockService->createStockOut([
            'tanggal' => $d3Out->toDateString(),
            'created_at' => $d3Out->copy()->setTime(15, 20),
            'tujuan' => 'Ruang Rapat Utama',
            'alasan' => 'pemakaian',
            'keterangan' => 'Kebutuhan rapat koordinasi triwulan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 12], // BRG-0009 tersisa 10 (menipis, min 12)
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 6],
            ],
        ], $staff);

        // 7. Bulan ke-2 lalu
        $d2In = now()->subMonths(2)->startOfMonth()->addDays(4);
        $stockService->createStockIn([
            'tanggal' => $d2In->toDateString(),
            'created_at' => $d2In->copy()->setTime(9, 30),
            'supplier_id' => $sup(4),
            'keterangan' => 'Restock lakban dan map plastik',
            'items' => [
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0028')],
            ],
        ], $staff);

        $d2Out = now()->subMonths(2)->startOfMonth()->addDays(19);
        $stockService->createStockOut([
            'tanggal' => $d2Out->toDateString(),
            'created_at' => $d2Out->copy()->setTime(14, 15),
            'tujuan' => 'Bagian Pengadaan & Arsip',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pemakaian rutin dokumen dan tinta printer',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 11], // BRG-0016 tersisa 7 (menipis, min 8)
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 10],
            ],
        ], $staff);

        // 8. Bulan lalu
        $d1In = now()->subMonth()->startOfMonth()->addDays(3);
        $stockService->createStockIn([
            'tanggal' => $d1In->toDateString(),
            'created_at' => $d1In->copy()->setTime(10, 0),
            'supplier_id' => $sup(0),
            'keterangan' => 'Penerimaan kertas HVS dan alat tulis bulanan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0010')],
            ],
        ], $staff);

        $d1Out = now()->subMonth()->startOfMonth()->addDays(15);
        $stockService->createStockOut([
            'tanggal' => $d1Out->toDateString(),
            'created_at' => $d1Out->copy()->setTime(15, 0),
            'tujuan' => 'Divisi Keuangan & Akuntansi',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pencetakan laporan keuangan bulanan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 9], // BRG-0031 tersisa 16 (menipis, min 20)
            ],
        ], $staff);

        // 9. Bulan berjalan (beberapa hari lalu & hari ini)
        $d0In = now()->subDays(min(3, max(1, now()->day - 1)));
        $stockService->createStockIn([
            'tanggal' => $d0In->toDateString(),
            'created_at' => $d0In->copy()->setTime(9, 20),
            'supplier_id' => $sup(1),
            'keterangan' => 'Pengadaan reguler awal bulan berjalan',
            'items' => [
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0014')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0022')],
            ],
        ], $staff);

        $d0Out = now()->subDays(min(1, max(0, now()->day - 1)));
        $stockService->createStockOut([
            'tanggal' => $d0Out->toDateString(),
            'created_at' => $d0Out->copy()->setTime(13, 45),
            'tujuan' => 'Divisi Pelayanan Akademik',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pengeluaran kertas HVS dan perlengkapan meja',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10], // BRG-0001 berakhir 8 rim (menipis, min 20)
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 8],
            ],
        ], $staff);
    }
}
