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
     * Buat riwayat transaksi barang masuk dan keluar yang tersebar merata selama 9 bulan melalui StockService (AGENTS.md §6.4, PRD §24).
     * Tiap bulan memiliki beberapa transaksi masuk dan keluar dengan jumlah serta alasan keluar yang bervariasi
     * (pemakaian, penjualan, rusak, dan koreksi dengan keterangan).
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

        // =====================================================================
        // 1. Bulan ke-8 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m8In1 = now()->subMonths(8)->startOfMonth()->addDays(2);
        $stockService->createStockIn([
            'tanggal' => $m8In1->toDateString(),
            'created_at' => $m8In1->copy()->setTime(8, 30),
            'supplier_id' => $sup(0),
            'keterangan' => 'Pengadaan awal kelompok Kertas & Dokumen',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 25, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0004')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0007')],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0008')],
            ],
        ], $admin);

        $m8Out1 = now()->subMonths(8)->startOfMonth()->addDays(10);
        $stockService->createStockOut([
            'tanggal' => $m8Out1->toDateString(),
            'created_at' => $m8Out1->copy()->setTime(13, 45),
            'tujuan' => 'Divisi Administrasi & Keuangan',
            'alasan' => 'pemakaian',
            'keterangan' => 'Distribusi kebutuhan dokumen operasional awal bulan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 5],
            ],
        ], $staff);

        $m8In2 = now()->subMonths(8)->startOfMonth()->addDays(14);
        $stockService->createStockIn([
            'tanggal' => $m8In2->toDateString(),
            'created_at' => $m8In2->copy()->setTime(9, 15),
            'supplier_id' => $sup(1),
            'keterangan' => 'Pengadaan awal kelompok Alat Tulis Kantor (ATK)',
            'items' => [
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0009')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 16, 'harga_beli' => $harga('BRG-0010')],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0011')],
                ['product_id' => $pid('BRG-0012'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0012')],
                ['product_id' => $pid('BRG-0013'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0013')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 25, 'harga_beli' => $harga('BRG-0014')],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 20, 'harga_beli' => $harga('BRG-0015')],
            ],
        ], $admin);

        $m8Out2 = now()->subMonths(8)->startOfMonth()->addDays(23);
        $stockService->createStockOut([
            'tanggal' => $m8Out2->toDateString(),
            'created_at' => $m8Out2->copy()->setTime(15, 20),
            'tujuan' => 'Koperasi Karyawan Sejahtera',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan perlengkapan ATK ke koperasi internal',
            'items' => [
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 5],
            ],
        ], $staff);

        // =====================================================================
        // 2. Bulan ke-7 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m7In1 = now()->subMonths(7)->startOfMonth()->addDays(3);
        $stockService->createStockIn([
            'tanggal' => $m7In1->toDateString(),
            'created_at' => $m7In1->copy()->setTime(9, 0),
            'supplier_id' => $sup(2),
            'keterangan' => 'Pengadaan tinta printer dan perangkat elektronik kecil',
            'items' => [
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0016')],
                ['product_id' => $pid('BRG-0017'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0017')],
                ['product_id' => $pid('BRG-0018'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0018')],
                ['product_id' => $pid('BRG-0019'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0019')],
                ['product_id' => $pid('BRG-0020'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0020')],
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0021')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0023'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0023')],
            ],
        ], $staff);

        $m7Out1 = now()->subMonths(7)->startOfMonth()->addDays(11);
        $stockService->createStockOut([
            'tanggal' => $m7Out1->toDateString(),
            'created_at' => $m7Out1->copy()->setTime(14, 10),
            'tujuan' => 'Bagian SDM & Umum',
            'alasan' => 'pemakaian',
            'keterangan' => 'Kebutuhan pelatihan karyawan baru dan operasional cetak',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 9],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 7],
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 5],
            ],
        ], $staff);

        $m7In2 = now()->subMonths(7)->startOfMonth()->addDays(16);
        $stockService->createStockIn([
            'tanggal' => $m7In2->toDateString(),
            'created_at' => $m7In2->copy()->setTime(10, 30),
            'supplier_id' => $sup(3),
            'keterangan' => 'Pengadaan perlengkapan kantor dan kebersihan gedung',
            'items' => [
                ['product_id' => $pid('BRG-0024'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0024')],
                ['product_id' => $pid('BRG-0025'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0025')],
                ['product_id' => $pid('BRG-0026'), 'jumlah' => 16, 'harga_beli' => $harga('BRG-0026')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0027')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 25, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0029'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0029')],
                ['product_id' => $pid('BRG-0030'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0030')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 22, 'harga_beli' => $harga('BRG-0031')],
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0032')],
            ],
        ], $staff);

        $m7Out2 = now()->subMonths(7)->startOfMonth()->addDays(25);
        $stockService->createStockOut([
            'tanggal' => $m7Out2->toDateString(),
            'created_at' => $m7Out2->copy()->setTime(16, 0),
            'tujuan' => 'CV Mitra Mandiri',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan grosir perlengkapan pengemasan dan kertas',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 6],
            ],
        ], $staff);

        // =====================================================================
        // 3. Bulan ke-6 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m6In1 = now()->subMonths(6)->startOfMonth()->addDays(4);
        $stockService->createStockIn([
            'tanggal' => $m6In1->toDateString(),
            'created_at' => $m6In1->copy()->setTime(9, 20),
            'supplier_id' => $sup(4),
            'keterangan' => 'Restock rutin kertas HVS, sticky notes, dan pulpen',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0008')],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0009')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0014')],
            ],
        ], $staff);

        $m6Out1 = now()->subMonths(6)->startOfMonth()->addDays(12);
        $stockService->createStockOut([
            'tanggal' => $m6Out1->toDateString(),
            'created_at' => $m6Out1->copy()->setTime(13, 30),
            'tujuan' => 'Divisi Operasional & Gudang',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pemakaian perlengkapan cetak dan pengemasan paket',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0008'), 'jumlah' => 10], // BRG-0008 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0018'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 7],
            ],
        ], $staff);

        $m6In2 = now()->subMonths(6)->startOfMonth()->addDays(18);
        $stockService->createStockIn([
            'tanggal' => $m6In2->toDateString(),
            'created_at' => $m6In2->copy()->setTime(10, 45),
            'supplier_id' => $sup(0),
            'keterangan' => 'Penambahan stok tinta, flashdisk, lakban, dan tisu',
            'items' => [
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0016')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 18, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0031')],
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0032')],
            ],
        ], $staff);

        $m6Out2 = now()->subMonths(6)->startOfMonth()->addDays(26);
        $stockService->createStockOut([
            'tanggal' => $m6Out2->toDateString(),
            'created_at' => $m6Out2->copy()->setTime(15, 50),
            'tujuan' => null,
            'alasan' => 'rusak',
            'keterangan' => 'Kemasan basah dan rusak akibat rembesan air di sudut rak gudang',
            'items' => [
                ['product_id' => $pid('BRG-0018'), 'jumlah' => 4], // BRG-0018 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0024'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0030'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 5],
            ],
        ], $admin);

        // =====================================================================
        // 4. Bulan ke-5 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m5In1 = now()->subMonths(5)->startOfMonth()->addDays(3);
        $stockService->createStockIn([
            'tanggal' => $m5In1->toDateString(),
            'created_at' => $m5In1->copy()->setTime(9, 10),
            'supplier_id' => $sup(1),
            'keterangan' => 'Pengadaan amplop, ordner, dan map plastik dokumen',
            'items' => [
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0004')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0010')],
            ],
        ], $staff);

        $m5Out1 = now()->subMonths(5)->startOfMonth()->addDays(11);
        $stockService->createStockOut([
            'tanggal' => $m5Out1->toDateString(),
            'created_at' => $m5Out1->copy()->setTime(14, 15),
            'tujuan' => 'Bagian Sekretariat',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pengarsipan dokumen semester dan penggantian baterai',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0024'), 'jumlah' => 8], // BRG-0024 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0026'), 'jumlah' => 8], // BRG-0026 tersisa 8 (menipis, min 10)
            ],
        ], $staff);

        $m5In2 = now()->subMonths(5)->startOfMonth()->addDays(17);
        $stockService->createStockIn([
            'tanggal' => $m5In2->toDateString(),
            'created_at' => $m5In2->copy()->setTime(11, 0),
            'supplier_id' => $sup(2),
            'keterangan' => 'Restock kertas A4, spidol, highlighter, dan mouse',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0011')],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0015')],
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0021')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0027')],
            ],
        ], $staff);

        $m5Out2 = now()->subMonths(5)->startOfMonth()->addDays(24);
        $stockService->createStockOut([
            'tanggal' => $m5Out2->toDateString(),
            'created_at' => $m5Out2->copy()->setTime(16, 10),
            'tujuan' => 'Toko Buku Kampus',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan paket alat tulis kantor dan map dokumen',
            'items' => [
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 6],
            ],
        ], $staff);

        // =====================================================================
        // 5. Bulan ke-4 lalu (2 Masuk, 2 Keluar — termasuk 1 koreksi)
        // =====================================================================
        $m4In1 = now()->subMonths(4)->startOfMonth()->addDays(5);
        $stockService->createStockIn([
            'tanggal' => $m4In1->toDateString(),
            'created_at' => $m4In1->copy()->setTime(8, 50),
            'supplier_id' => $sup(3),
            'keterangan' => 'Penerimaan kertas F4, buku agenda, spidol permanen, dan pensil',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0007')],
                ['product_id' => $pid('BRG-0012'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0012')],
                ['product_id' => $pid('BRG-0013'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0013')],
                ['product_id' => $pid('BRG-0017'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0017')],
            ],
        ], $staff);

        $m4Out1 = now()->subMonths(4)->startOfMonth()->addDays(13);
        $stockService->createStockOut([
            'tanggal' => $m4Out1->toDateString(),
            'created_at' => $m4Out1->copy()->setTime(14, 30),
            'tujuan' => 'Divisi IT & Layanan Teknis',
            'alasan' => 'pemakaian',
            'keterangan' => 'Distribusi perangkat mouse, flashdisk, dan penjepit dokumen',
            'items' => [
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0030'), 'jumlah' => 10], // BRG-0030 menjadi 0 (habis)
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 8],
            ],
        ], $staff);

        $m4In2 = now()->subMonths(4)->startOfMonth()->addDays(19);
        $stockService->createStockIn([
            'tanggal' => $m4In2->toDateString(),
            'created_at' => $m4In2->copy()->setTime(10, 15),
            'supplier_id' => $sup(4),
            'keterangan' => 'Pengadaan toner printer, kalkulator, kabel ekstensi, dan cairan pembersih',
            'items' => [
                ['product_id' => $pid('BRG-0019'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0019')],
                ['product_id' => $pid('BRG-0020'), 'jumlah' => 6, 'harga_beli' => $harga('BRG-0020')],
                ['product_id' => $pid('BRG-0023'), 'jumlah' => 6, 'harga_beli' => $harga('BRG-0023')],
                ['product_id' => $pid('BRG-0025'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0025')],
                ['product_id' => $pid('BRG-0029'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0029')],
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0032')],
            ],
        ], $staff);

        $m4Out2 = now()->subMonths(4)->startOfMonth()->addDays(26);
        $stockService->createStockOut([
            'tanggal' => $m4Out2->toDateString(),
            'created_at' => $m4Out2->copy()->setTime(16, 20),
            'tujuan' => null,
            'alasan' => 'koreksi',
            'keterangan' => 'Koreksi selisih hitung fisik gudang atas penerimaan bulan berjalan',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0012'), 'jumlah' => 3],
                ['product_id' => $pid('BRG-0013'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0029'), 'jumlah' => 4],
            ],
        ], $admin);

        // =====================================================================
        // 6. Bulan ke-3 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m3In1 = now()->subMonths(3)->startOfMonth()->addDays(4);
        $stockService->createStockIn([
            'tanggal' => $m3In1->toDateString(),
            'created_at' => $m3In1->copy()->setTime(9, 40),
            'supplier_id' => $sup(0),
            'keterangan' => 'Penerimaan kertas HVS, pulpen, spidol whiteboard, dan pita koreksi',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 16, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0009')],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0011')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0014')],
            ],
        ], $staff);

        $m3Out1 = now()->subMonths(3)->startOfMonth()->addDays(12);
        $stockService->createStockOut([
            'tanggal' => $m3Out1->toDateString(),
            'created_at' => $m3Out1->copy()->setTime(14, 0),
            'tujuan' => 'Ruang Rapat Utama',
            'alasan' => 'pemakaian',
            'keterangan' => 'Kebutuhan rapat koordinasi triwulan dan lokakarya',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0011'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 6],
            ],
        ], $staff);

        $m3In2 = now()->subMonths(3)->startOfMonth()->addDays(18);
        $stockService->createStockIn([
            'tanggal' => $m3In2->toDateString(),
            'created_at' => $m3In2->copy()->setTime(11, 10),
            'supplier_id' => $sup(1),
            'keterangan' => 'Restock amplop coklat, ordner, tinta hitam, lakban, dan tisu',
            'items' => [
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0004')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0016')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0031')],
            ],
        ], $staff);

        $m3Out2 = now()->subMonths(3)->startOfMonth()->addDays(25);
        $stockService->createStockOut([
            'tanggal' => $m3Out2->toDateString(),
            'created_at' => $m3Out2->copy()->setTime(15, 35),
            'tujuan' => 'Unit Usaha Koperasi',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan perlengkapan arsip dan pengemasan',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0004'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 8],
            ],
        ], $staff);

        // =====================================================================
        // 7. Bulan ke-2 lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m2In1 = now()->subMonths(2)->startOfMonth()->addDays(3);
        $stockService->createStockIn([
            'tanggal' => $m2In1->toDateString(),
            'created_at' => $m2In1->copy()->setTime(9, 15),
            'supplier_id' => $sup(2),
            'keterangan' => 'Penerimaan kertas A4, map plastik, pulpen gel, dan flashdisk',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 14, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0010')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0027')],
            ],
        ], $staff);

        $m2Out1 = now()->subMonths(2)->startOfMonth()->addDays(11);
        $stockService->createStockOut([
            'tanggal' => $m2Out1->toDateString(),
            'created_at' => $m2Out1->copy()->setTime(14, 15),
            'tujuan' => 'Bagian Pengadaan & Arsip',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pemakaian rutin dokumen, tinta printer, dan mouse wireless',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10],
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 9],
                ['product_id' => $pid('BRG-0021'), 'jumlah' => 6], // BRG-0021 tersisa 4 (menipis, min 5)
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 8],
            ],
        ], $staff);

        $m2In2 = now()->subMonths(2)->startOfMonth()->addDays(17);
        $stockService->createStockIn([
            'tanggal' => $m2In2->toDateString(),
            'created_at' => $m2In2->copy()->setTime(10, 30),
            'supplier_id' => $sup(3),
            'keterangan' => 'Restock amplop putih, highlighter, lakban, tisu, dan pembersih lantai',
            'items' => [
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0015')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0031')],
                ['product_id' => $pid('BRG-0032'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0032')],
            ],
        ], $staff);

        $m2Out2 = now()->subMonths(2)->startOfMonth()->addDays(24);
        $stockService->createStockOut([
            'tanggal' => $m2Out2->toDateString(),
            'created_at' => $m2Out2->copy()->setTime(16, 0),
            'tujuan' => null,
            'alasan' => 'rusak',
            'keterangan' => 'Tinta mengering dan kemasan pulpen penyok saat pemindahan rak',
            'items' => [
                ['product_id' => $pid('BRG-0009'), 'jumlah' => 12], // BRG-0009 tersisa 10 (menipis, min 12)
                ['product_id' => $pid('BRG-0016'), 'jumlah' => 7],  // BRG-0016 tersisa 7 (menipis, min 8)
                ['product_id' => $pid('BRG-0017'), 'jumlah' => 4],
                ['product_id' => $pid('BRG-0019'), 'jumlah' => 4],
            ],
        ], $admin);

        // =====================================================================
        // 8. Bulan lalu (2 Masuk, 2 Keluar)
        // =====================================================================
        $m1In1 = now()->subMonthNoOverflow()->startOfMonth();
        $stockService->createStockIn([
            'tanggal' => $m1In1->toDateString(),
            'created_at' => $m1In1->copy()->setTime(9, 30),
            'supplier_id' => $sup(0),
            'keterangan' => 'Penerimaan kertas HVS dan alat tulis awal bulan lalu',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 15, 'harga_beli' => $harga('BRG-0001')],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0010')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 12, 'harga_beli' => $harga('BRG-0014')],
            ],
        ], $staff);

        $m1Out1 = now()->subMonthNoOverflow()->startOfMonth()->addDay();
        $stockService->createStockOut([
            'tanggal' => $m1Out1->toDateString(),
            'created_at' => $m1Out1->copy()->setTime(14, 20),
            'tujuan' => 'Divisi Keuangan & Akuntansi',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pencetakan laporan keuangan bulanan dan bundel audit',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 12],
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0010'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 6],
            ],
        ], $staff);

        $m1In2 = now()->subMonthNoOverflow()->startOfMonth()->addDays(14);
        $stockService->createStockIn([
            'tanggal' => $m1In2->toDateString(),
            'created_at' => $m1In2->copy()->setTime(10, 15),
            'supplier_id' => $sup(4),
            'keterangan' => 'Restock ordner, map plastik, flashdisk, lakban, dan tisu',
            'items' => [
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0006'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0006')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0031')],
            ],
        ], $staff);

        $m1Out2 = now()->subMonthNoOverflow()->startOfMonth()->addDays(21);
        $stockService->createStockOut([
            'tanggal' => $m1Out2->toDateString(),
            'created_at' => $m1Out2->copy()->setTime(15, 45),
            'tujuan' => 'Koperasi Karyawan Sejahtera',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan rutin kertas, amplop, pita koreksi, dan lakban',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 11],
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 8],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 7],
            ],
        ], $staff);

        // =====================================================================
        // 9. Bulan berjalan (2 Masuk, 2 Keluar)
        // =====================================================================
        $d0In1 = now()->subDays(min(5, max(2, now()->day - 1)));
        $stockService->createStockIn([
            'tanggal' => $d0In1->toDateString(),
            'created_at' => $d0In1->copy()->setTime(9, 20),
            'supplier_id' => $sup(1),
            'keterangan' => 'Pengadaan reguler awal bulan berjalan',
            'items' => [
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0003')],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0014')],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0022')],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0028')],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0031')],
            ],
        ], $staff);

        $d0Out1 = now()->subDays(min(4, max(2, now()->day - 1)));
        $stockService->createStockOut([
            'tanggal' => $d0Out1->toDateString(),
            'created_at' => $d0Out1->copy()->setTime(13, 45),
            'tujuan' => 'Divisi Pelayanan Akademik',
            'alasan' => 'pemakaian',
            'keterangan' => 'Pengeluaran kertas HVS, amplop, dan perlengkapan ruang layanan',
            'items' => [
                ['product_id' => $pid('BRG-0001'), 'jumlah' => 10], // BRG-0001 berakhir 15 rim (menipis, min 20)
                ['product_id' => $pid('BRG-0003'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0031'), 'jumlah' => 15],
            ],
        ], $staff);

        $d0In2 = now()->subDays(min(2, max(1, now()->day - 1)));
        $stockService->createStockIn([
            'tanggal' => $d0In2->toDateString(),
            'created_at' => $d0In2->copy()->setTime(10, 10),
            'supplier_id' => $sup(2),
            'keterangan' => 'Penambahan stok kertas F4, ordner, buku agenda, dan isi staples',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 10, 'harga_beli' => $harga('BRG-0002')],
                ['product_id' => $pid('BRG-0005'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0005')],
                ['product_id' => $pid('BRG-0007'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0007')],
                ['product_id' => $pid('BRG-0015'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0015')],
                ['product_id' => $pid('BRG-0027'), 'jumlah' => 8, 'harga_beli' => $harga('BRG-0027')],
            ],
        ], $staff);

        $d0Out2 = now()->subDays(min(1, max(0, now()->day - 1)));
        $stockService->createStockOut([
            'tanggal' => $d0Out2->toDateString(),
            'created_at' => $d0Out2->copy()->setTime(15, 0),
            'tujuan' => 'CV Mitra Mandiri',
            'alasan' => 'penjualan',
            'keterangan' => 'Penjualan kertas F4, pita koreksi, flashdisk, dan lakban',
            'items' => [
                ['product_id' => $pid('BRG-0002'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0014'), 'jumlah' => 6],
                ['product_id' => $pid('BRG-0022'), 'jumlah' => 5],
                ['product_id' => $pid('BRG-0028'), 'jumlah' => 8],
            ],
        ], $staff);
    }
}
