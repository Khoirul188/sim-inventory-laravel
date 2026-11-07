<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\StockService;
use Carbon\Carbon;
use Database\Seeders\RoleAndUserSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_empty_database_renders_cleanly_and_hides_low_stock_badges(): void
    {
        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Semua stok di atas batas minimum.');
        $response->assertSee('Belum ada barang. Tambahkan barang pertama Anda.');
        $response->assertSee('Belum ada stok per kategori');
        $response->assertSee('Belum ada barang masuk');
        $response->assertSee('Belum ada pengeluaran 30 hari terakhir');
        $response->assertSee('Belum ada aktivitas');

        $data = app(DashboardService::class)->getDashboardData('12m');

        $this->assertSame(0, $data['kpis']['totalProducts']);
        $this->assertSame(0, $data['kpis']['totalStock']);
        $this->assertSame('—', $data['kpis']['inTrendBadge']);
        $this->assertSame('—', $data['kpis']['outTrendBadge']);
        $this->assertSame(0, $data['lowStockCount']);
        $this->assertSame(0, $data['outOfStockCount']);
        $this->assertCount(12, $data['movementChart']['labels']);
    }

    public function test_dashboard_metrics_match_manual_calculations_including_soft_delete_and_zero_baseline(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 12, 0, 0, 'Asia/Jakarta'));

        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $this->actingAs($admin);

        $unit = Unit::firstOrFail();
        $supplier = Supplier::create([
            'kode_supplier' => 'SUP-001',
            'nama' => 'PT Pemasok Uji',
            'telepon' => '021-5551234',
        ]);

        // Buat 5 kategori untuk menguji 4 kategori teratas + "Lainnya"
        $cat1 = Category::create(['nama' => 'Kategori Satu']);
        $cat2 = Category::create(['nama' => 'Kategori Dua']);
        $cat3 = Category::create(['nama' => 'Kategori Tiga']);
        $cat4 = Category::create(['nama' => 'Kategori Empat']);
        $cat5 = Category::create(['nama' => 'Kategori Lima']);

        // P1: dibuat 3 hari lalu (masuk hitungan minggu ini), stok akhir = 60, min = 10 (Aman)
        $p1 = Product::create([
            'kode_barang' => 'BRG-0001',
            'nama' => 'Barang Aman Satu',
            'category_id' => $cat1->id,
            'unit_id' => $unit->id,
            'harga_beli' => 10000,
            'stok' => 0,
            'stok_minimum' => 10,
        ]);
        Product::whereKey($p1->id)->update(['created_at' => now()->subDays(3)]);

        // P2: dibuat 20 hari lalu (bukan minggu ini), stok akhir = 8, min = 20 (Menipis, rasio 40%)
        $p2 = Product::create([
            'kode_barang' => 'BRG-0002',
            'nama' => 'Barang Menipis Dua',
            'category_id' => $cat2->id,
            'unit_id' => $unit->id,
            'harga_beli' => 20000,
            'stok' => 0,
            'stok_minimum' => 20,
        ]);
        Product::whereKey($p2->id)->update(['created_at' => now()->subDays(20)]);

        // P3: dibuat 2 hari lalu (masuk hitungan minggu ini), stok akhir = 6, min = 15 (Menipis, rasio 40%)
        $p3 = Product::create([
            'kode_barang' => 'BRG-0003',
            'nama' => 'Barang Menipis Tiga',
            'category_id' => $cat3->id,
            'unit_id' => $unit->id,
            'harga_beli' => 25000,
            'stok' => 0,
            'stok_minimum' => 15,
        ]);
        Product::whereKey($p3->id)->update(['created_at' => now()->subDays(2)]);

        // P4: dibuat 15 hari lalu, stok akhir = 10, min = 5 (Aman)
        $p4 = Product::create([
            'kode_barang' => 'BRG-0004',
            'nama' => 'Barang Aman Empat',
            'category_id' => $cat4->id,
            'unit_id' => $unit->id,
            'harga_beli' => 15000,
            'stok' => 0,
            'stok_minimum' => 5,
        ]);
        Product::whereKey($p4->id)->update(['created_at' => now()->subDays(15)]);

        // P5: dibuat 1 hari lalu (masuk hitungan minggu ini), stok akhir = 0, min = 10 (Habis & ikut Menipis)
        $p5 = Product::create([
            'kode_barang' => 'BRG-0005',
            'nama' => 'Barang Habis Lima',
            'category_id' => $cat5->id,
            'unit_id' => $unit->id,
            'harga_beli' => 30000,
            'stok' => 0,
            'stok_minimum' => 10,
        ]);
        Product::whereKey($p5->id)->update(['created_at' => now()->subDays(1)]);

        // P6: di Kategori Lima (dibuat 12 hari lalu), stok akhir = 4, min = 2 (Aman, mengisi segmen ke-5 "Lainnya")
        $p6 = Product::create([
            'kode_barang' => 'BRG-0006',
            'nama' => 'Barang Aman Enam',
            'category_id' => $cat5->id,
            'unit_id' => $unit->id,
            'harga_beli' => 12000,
            'stok' => 0,
            'stok_minimum' => 2,
        ]);
        Product::whereKey($p6->id)->update(['created_at' => now()->subDays(12)]);

        // P7: barang yang akan di-soft delete setelah punya stok dan transaksi
        $p7 = Product::create([
            'kode_barang' => 'BRG-0007',
            'nama' => 'Barang Terhapus Tujuh',
            'category_id' => $cat1->id,
            'unit_id' => $unit->id,
            'harga_beli' => 50000,
            'stok' => 0,
            'stok_minimum' => 100,
        ]);
        Product::whereKey($p7->id)->update(['created_at' => now()->subDays(1)]);

        $stockService = app(StockService::class);

        // 1) Transaksi Masuk bulan lalu pada rentang tanggal yang sama (10 Okt 2026)
        $lastMonthDate = Carbon::create(2026, 10, 10, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($lastMonthDate);
        $stockService->createStockIn([
            'tanggal' => $lastMonthDate->toDateString(),
            'supplier_id' => $supplier->id,
            'keterangan' => 'Stok bulan lalu',
            'items' => [
                ['product_id' => $p1->id, 'jumlah' => 40, 'harga_beli' => 10000],
                ['product_id' => $p2->id, 'jumlah' => 10, 'harga_beli' => 20000],
                ['product_id' => $p3->id, 'jumlah' => 15, 'harga_beli' => 25000],
                ['product_id' => $p4->id, 'jumlah' => 12, 'harga_beli' => 15000],
                ['product_id' => $p6->id, 'jumlah' => 4, 'harga_beli' => 12000],
                ['product_id' => $p7->id, 'jumlah' => 50, 'harga_beli' => 50000],
            ],
        ], $admin);

        // 2) Transaksi Masuk bulan ini, 10 hari lalu (05 Nov 2026 — di luar 7 hari, di dalam bulan ini)
        $tenDaysAgo = Carbon::create(2026, 11, 5, 9, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($tenDaysAgo);
        $stockService->createStockIn([
            'tanggal' => $tenDaysAgo->toDateString(),
            'supplier_id' => $supplier->id,
            'keterangan' => 'Restok awal bulan',
            'items' => [
                ['product_id' => $p1->id, 'jumlah' => 20, 'harga_beli' => 10000],
                ['product_id' => $p2->id, 'jumlah' => 5, 'harga_beli' => 20000],
            ],
        ], $admin);

        // 3) Transaksi Masuk & Keluar bulan ini, 3 hari lalu (12 Nov 2026 — di dalam 7 hari terakhir)
        $threeDaysAgo = Carbon::create(2026, 11, 12, 14, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($threeDaysAgo);
        $stockService->createStockIn([
            'tanggal' => $threeDaysAgo->toDateString(),
            'supplier_id' => $supplier->id,
            'keterangan' => 'Restok minggu ini',
            'items' => [
                ['product_id' => $p3->id, 'jumlah' => 3, 'harga_beli' => 25000],
            ],
        ], $admin);

        $stockService->createStockOut([
            'tanggal' => $threeDaysAgo->toDateString(),
            'alasan' => 'pemakaian',
            'tujuan' => 'Divisi Operasional',
            'keterangan' => 'Pemakaian rutin',
            'items' => [
                ['product_id' => $p2->id, 'jumlah' => 7],
                ['product_id' => $p3->id, 'jumlah' => 12],
                ['product_id' => $p4->id, 'jumlah' => 2],
            ],
        ], $admin);

        // Kembalikan waktu tes ke 15 Nov 2026 dan soft-delete P7
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 12, 0, 0, 'Asia/Jakarta'));
        $p7->delete();

        $service = app(DashboardService::class);
        $data12m = $service->getDashboardData('12m');
        $data30d = $service->getDashboardData('30d');

        // Verifikasi KPI terhadap hitungan manual:
        // Barang aktif = 6 (P1..P6, P7 soft-deleted tidak dihitung)
        // Barang baru 7 hari terakhir = 3 (P1, P3, P5)
        $this->assertSame(6, $data12m['kpis']['totalProducts']);
        $this->assertSame(3, $data12m['kpis']['newProductsThisWeek']);
        $this->assertSame('+3', $data12m['kpis']['newProductsBadge']);

        // Total stok aktif = 60 (P1) + 8 (P2) + 6 (P3) + 10 (P4) + 0 (P5) + 4 (P6) = 88 (stok P7 = 50 diabaikan)
        $this->assertSame(88, $data12m['kpis']['totalStock']);

        // Selisih bersih 7 hari = masuk (3) - keluar (7 + 12 + 2 = 21) = -18
        $this->assertSame(-18, $data12m['kpis']['net7Days']);
        $this->assertSame('▼ −18', $data12m['kpis']['net7DaysBadge']);
        $this->assertSame('tren-turun', $data12m['kpis']['net7DaysVariant']);

        // Barang masuk bulan ini = 20 + 5 + 3 = 28; bulan lalu (barang aktif) = 40 + 10 + 15 + 12 + 4 = 81
        // Tren masuk = (28 - 81) / 81 * 100 = -65.4%
        $this->assertSame(28, $data12m['kpis']['monthlyIn']);
        $this->assertSame(81, $data12m['kpis']['lastMonthIn']);
        $this->assertSame(-65.4, $data12m['kpis']['inTrendPercent']);
        $this->assertSame('▼ −65,4%', $data12m['kpis']['inTrendBadge']);
        $this->assertSame('tren-turun', $data12m['kpis']['inTrendVariant']);

        // Barang keluar bulan ini = 7 + 12 + 2 = 21; bulan lalu = 0 -> tren wajib "—"
        $this->assertSame(21, $data12m['kpis']['monthlyOut']);
        $this->assertSame(0, $data12m['kpis']['lastMonthOut']);
        $this->assertNull($data12m['kpis']['outTrendPercent']);
        $this->assertSame('—', $data12m['kpis']['outTrendBadge']);
        $this->assertSame('neutral', $data12m['kpis']['outTrendVariant']);

        // Level stok: Menipis = 3 (P2, P3, dan P5 yang habis ikut dihitung menipis); Habis = 1 (P5)
        $this->assertSame(3, $data12m['lowStockCount']);
        $this->assertSame(1, $data12m['outOfStockCount']);
        $this->assertCount(3, $data12m['criticalProducts']);
        $this->assertSame($p5->id, $data12m['criticalProducts'][0]['product']->id);
        $this->assertSame(0, $data12m['criticalProducts'][0]['ratio_percent']);

        // Grafik Masuk vs Keluar: 12m memiliki 12 titik (bulan kosong diisi 0), 30d memiliki 30 titik
        $this->assertCount(12, $data12m['movementChart']['labels']);
        $this->assertSame(28, end($data12m['movementChart']['in']));
        $this->assertSame(21, end($data12m['movementChart']['out']));
        $this->assertSame(0, $data12m['movementChart']['in'][0]);

        $this->assertCount(30, $data30d['movementChart']['labels']);
        $this->assertSame(28, array_sum($data30d['movementChart']['in']));
        $this->assertSame(21, array_sum($data30d['movementChart']['out']));

        // Stok per Kategori: 4 kategori teratas + "Lainnya" (Kat 5 = 4 unit)
        $this->assertCount(5, $data12m['categoryStock']['segments']);
        $this->assertSame('Kategori Satu', $data12m['categoryStock']['segments'][0]['label']);
        $this->assertSame(60, $data12m['categoryStock']['segments'][0]['stok']);
        $this->assertSame('Lainnya', $data12m['categoryStock']['segments'][4]['label']);
        $this->assertSame(4, $data12m['categoryStock']['segments'][4]['stok']);

        // Barang Paling Sering Keluar (30 hari): P3 (12 unit = 57,1%), P2 (7 unit = 33,3%), P4 (2 unit = 9,5%)
        $this->assertCount(3, $data12m['topOutgoingProducts']);
        $this->assertSame($p3->id, $data12m['topOutgoingProducts'][0]['product']->id);
        $this->assertSame(12, $data12m['topOutgoingProducts'][0]['total_out']);
        $this->assertSame(57.1, $data12m['topOutgoingProducts'][0]['percent']);
        $this->assertSame([0, 0, 0, 12], $data12m['topOutgoingProducts'][0]['sparkline']);

        // Aktivitas Terbaru: 4 transaksi (3 StockIn + 1 StockOut)
        $this->assertCount(4, $data12m['recentActivities']);
    }

    public function test_dashboard_access_per_role_and_no_n_plus_one_queries(): void
    {
        $this->seed();

        // Tamu dialihkan ke halaman login
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();

        // Admin melihat tombol aksi
        $adminResponse = $this->actingAs($admin)->get(route('dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Tambah Barang');
        $adminResponse->assertSee('Catat Barang Masuk');

        // Staff melihat tombol aksi
        $staffResponse = $this->actingAs($staff)->get(route('dashboard'));
        $staffResponse->assertOk();
        $staffResponse->assertSee('Tambah Barang');
        $staffResponse->assertSee('Catat Barang Masuk');

        // Pimpinan hanya melihat versi baca saja (tanpa tombol aksi tambah barang / catat barang masuk)
        DB::enableQueryLog();
        DB::flushQueryLog();

        $pimpinanResponse = $this->actingAs($pimpinan)->get(route('dashboard'));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $pimpinanResponse->assertOk();
        $pimpinanResponse->assertDontSee('+ Tambah Barang');
        $pimpinanResponse->assertDontSee('+ Catat Barang Masuk');

        // Pastikan tidak ada N+1 query: seluruh relasi dimuat dengan eager loading (WHERE ... IN), bukan query berulang per baris
        $this->assertLessThanOrEqual(32, count($queries));

        $singleUnitQueries = collect($queries)->filter(function (array $q) {
            return str_contains($q['query'], 'from "units" where "units"."id" =')
                || str_contains($q['query'], 'from `units` where `units`.`id` =');
        });
        $this->assertCount(0, $singleUnitQueries);

        // Pastikan hitungan lowStock (stok <= stok_minimum) hanya dieksekusi satu kali untuk seluruh halaman + topbar + sidebar
        $lowStockCountQueries = collect($queries)->filter(function (array $q) {
            return str_contains($q['query'], 'count(*)') && str_contains($q['query'], 'stok_minimum');
        });
        $this->assertCount(1, $lowStockCountQueries);
    }
}
