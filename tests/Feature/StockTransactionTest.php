<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $pimpinan;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $this->pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();
        $this->stockService = app(StockService::class);
    }

    public function test_tc01_tc02_tc03_tc05_stock_in_and_out_lifecycle(): void
    {
        $category = Category::firstOrFail();
        $unit = Unit::firstOrFail();
        $supplier = Supplier::firstOrFail();

        $product = Product::create([
            'kode_barang' => 'BRG-0901',
            'nama' => 'Kertas Uji Transaksi',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'harga_beli' => 50000,
            'stok' => 0,
            'stok_minimum' => 8,
        ]);

        // Stok awal 3 unit lewat StockService
        $this->stockService->createStockIn([
            'tanggal' => now()->subDays(2)->toDateString(),
            'supplier_id' => $supplier->id,
            'keterangan' => 'Stok awal 3 unit',
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 3, 'harga_beli' => 50000],
            ],
        ], $this->staff);

        $this->assertSame(3, $product->fresh()->stok);

        // TC-01: Barang masuk 10 unit untuk barang bersetok 3 -> Stok 13; kartu stok IN (sebelum 3, sesudah 13)
        $this->actingAs($this->staff)
            ->post(route('stock-ins.store'), [
                'tanggal' => now()->toDateString(),
                'supplier_id' => $supplier->id,
                'keterangan' => 'Penambahan 10 unit',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 10, 'harga_beli' => 52000],
                ],
            ])
            ->assertRedirect();

        $product->refresh();
        $this->assertSame(13, $product->stok);

        $lastMovementIn = StockMovement::where('product_id', $product->id)->latest('id')->firstOrFail();
        $this->assertSame('in', $lastMovementIn->tipe);
        $this->assertSame(10, $lastMovementIn->jumlah);
        $this->assertSame(3, $lastMovementIn->stok_sebelum);
        $this->assertSame(13, $lastMovementIn->stok_sesudah);

        // TC-02 & TC-05: Barang keluar 5 dari stok 13 -> Stok 8 (= stok_minimum 8 -> status menipis); kartu stok OUT
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'tujuan' => 'Bagian Keuangan',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 5],
                ],
            ])
            ->assertRedirect();

        $product->refresh();
        $this->assertSame(8, $product->stok);
        $this->assertSame('menipis', $product->status_stok);
        $this->assertTrue(Product::lowStock()->where('id', $product->id)->exists());

        $lastMovementOut = StockMovement::where('product_id', $product->id)->latest('id')->firstOrFail();
        $this->assertSame('out', $lastMovementOut->tipe);
        $this->assertSame(5, $lastMovementOut->jumlah);
        $this->assertSame(13, $lastMovementOut->stok_sebelum);
        $this->assertSame(8, $lastMovementOut->stok_sesudah);

        // TC-03: Barang keluar 20 dari stok 8 -> Ditolak; stok tetap 8; tidak ada transaksi tersimpan
        $stockOutCountBefore = StockOut::count();

        $this->actingAs($this->staff)
            ->from(route('stock-outs.create'))
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'tujuan' => 'Bagian Umum',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 20],
                ],
            ])
            ->assertRedirect(route('stock-outs.create'))
            ->assertSessionHasErrors([
                'items.0.jumlah' => "Stok {$product->nama} tidak cukup. Tersedia 8, diminta 20.",
            ]);

        $this->assertSame(8, $product->fresh()->stok);
        $this->assertSame($stockOutCountBefore, StockOut::count());
    }

    public function test_tc04_tc11_rollback_when_second_item_fails_and_sequential_depletion(): void
    {
        $category = Category::firstOrFail();
        $unit = Unit::firstOrFail();

        $productA = Product::create([
            'kode_barang' => 'BRG-0902',
            'nama' => 'Barang Uji A',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stok' => 0,
            'stok_minimum' => 2,
        ]);

        $productB = Product::create([
            'kode_barang' => 'BRG-0903',
            'nama' => 'Barang Uji B',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stok' => 0,
            'stok_minimum' => 2,
        ]);

        $this->stockService->createStockIn([
            'tanggal' => now()->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'jumlah' => 10, 'harga_beli' => 10000],
                ['product_id' => $productB->id, 'jumlah' => 4, 'harga_beli' => 15000],
            ],
        ], $this->staff);

        $movementsCountBefore = StockMovement::count();
        $stockOutsCountBefore = StockOut::count();

        // TC-04: Dua item dalam satu transaksi, item pertama cukup (5 dari 10), item kedua gagal (9 dari 4)
        $exceptionThrown = false;
        try {
            $this->stockService->createStockOut([
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'tujuan' => 'Divisi Operasional',
                'items' => [
                    ['product_id' => $productA->id, 'jumlah' => 5],
                    ['product_id' => $productB->id, 'jumlah' => 9],
                ],
            ], $this->staff);
        } catch (InsufficientStockException $e) {
            $exceptionThrown = true;
            $this->assertSame($productB->id, $e->product->id);
            $this->assertSame(9, $e->requestedQty);
        }

        $this->assertTrue($exceptionThrown);
        $this->assertSame(10, $productA->fresh()->stok);
        $this->assertSame(4, $productB->fresh()->stok);
        $this->assertSame($movementsCountBefore, StockMovement::count());
        $this->assertSame($stockOutsCountBefore, StockOut::count());

        // TC-11: Dua permintaan mengeluarkan stok yang hanya cukup untuk satu (stok B = 4, diminta 3 lalu 3)
        $this->stockService->createStockOut([
            'tanggal' => now()->toDateString(),
            'alasan' => 'pemakaian',
            'items' => [
                ['product_id' => $productB->id, 'jumlah' => 3],
            ],
        ], $this->staff);

        $this->assertSame(1, $productB->fresh()->stok);

        $this->expectException(InsufficientStockException::class);
        try {
            $this->stockService->createStockOut([
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'items' => [
                    ['product_id' => $productB->id, 'jumlah' => 3],
                ],
            ], $this->staff);
        } finally {
            $this->assertSame(1, $productB->fresh()->stok);
        }
    }

    public function test_tc06_tc07_tc08_tc09_validation_rules(): void
    {
        $product = Product::where('stok', '>', 5)->firstOrFail();

        // TC-06: Barang dobel dalam satu transaksi -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-ins.store'), [
                'tanggal' => now()->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 2],
                    ['product_id' => $product->id, 'jumlah' => 3],
                ],
            ])
            ->assertSessionHasErrors([
                'items.0.product_id' => 'Barang yang sama tidak boleh dimasukkan dua kali. Gabungkan jumlahnya.',
            ]);

        // TC-07: Jumlah 0 atau negatif -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-ins.store'), [
                'tanggal' => now()->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 0],
                ],
            ])
            ->assertSessionHasErrors([
                'items.0.jumlah' => 'Jumlah harus bilangan bulat lebih dari 0.',
            ]);

        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'tujuan' => 'Bagian Umum',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => -3],
                ],
            ])
            ->assertSessionHasErrors([
                'items.0.jumlah' => 'Jumlah harus bilangan bulat lebih dari 0.',
            ]);

        // TC-08: Tanggal masa depan -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-ins.store'), [
                'tanggal' => now()->addDay()->toDateString(),
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 5],
                ],
            ])
            ->assertSessionHasErrors([
                'tanggal' => 'Tanggal tidak boleh melebihi hari ini.',
            ]);

        // TC-09a: Alasan kosong -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => '',
                'tujuan' => 'Bagian Umum',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasErrors([
                'alasan' => 'Alasan wajib dipilih.',
            ]);

        // TC-09b: Alasan pemakaian tanpa tujuan -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'pemakaian',
                'tujuan' => '',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasErrors([
                'tujuan' => 'Tujuan / penerima wajib diisi untuk alasan ini.',
            ]);

        // TC-09c: Alasan penjualan tanpa tujuan -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'penjualan',
                'tujuan' => '',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasErrors([
                'tujuan' => 'Tujuan / penerima wajib diisi untuk alasan ini.',
            ]);

        // TC-09d: Alasan koreksi tanpa keterangan -> ditolak
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'koreksi',
                'keterangan' => '',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasErrors([
                'keterangan' => 'Keterangan wajib diisi untuk alasan ini.',
            ]);

        // TC-09e: Alasan rusak tanpa tujuan -> diterima
        $stokSebelum = $product->fresh()->stok;
        $this->actingAs($this->staff)
            ->post(route('stock-outs.store'), [
                'tanggal' => now()->toDateString(),
                'alasan' => 'rusak',
                'tujuan' => '',
                'keterangan' => 'Kemasan lembap',
                'items' => [
                    ['product_id' => $product->id, 'jumlah' => 1],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame($stokSebelum - 1, $product->fresh()->stok);
    }

    public function test_product_dropdown_in_transaction_forms_is_ordered_by_kode_barang(): void
    {
        $expectedCodes = Product::orderBy('kode_barang')->pluck('kode_barang')->all();

        $stockOutCreate = $this->actingAs($this->staff)->get(route('stock-outs.create'));
        $stockOutCreate->assertOk()
            ->assertSee('Pilih alasan…')
            ->assertSeeInOrder($expectedCodes)
            ->assertSee("product.kode + ' — ' + product.nama + ' (stok ' + product.stok + ' ' + product.satuan + ')'", false);

        $stockInCreate = $this->actingAs($this->staff)->get(route('stock-ins.create'));
        $stockInCreate->assertOk()
            ->assertSeeInOrder($expectedCodes)
            ->assertSee("product.kode + ' — ' + product.nama + ' (stok ' + product.stok + ' ' + product.satuan + ')'", false);
    }

    public function test_tc10_daily_sequential_transaction_numbers_and_immutability(): void
    {
        $product = Product::where('stok', '>', 5)->firstOrFail();
        $date = now()->subDays(10)->toDateString();
        $dateCode = now()->subDays(10)->format('Ymd');

        $firstIn = $this->stockService->createStockIn([
            'tanggal' => $date,
            'items' => [['product_id' => $product->id, 'jumlah' => 2]],
        ], $this->staff);

        $secondIn = $this->stockService->createStockIn([
            'tanggal' => $date,
            'items' => [['product_id' => $product->id, 'jumlah' => 3]],
        ], $this->staff);

        $this->assertSame("BM-{$dateCode}-001", $firstIn->no_transaksi);
        $this->assertSame("BM-{$dateCode}-002", $secondIn->no_transaksi);

        $firstOut = $this->stockService->createStockOut([
            'tanggal' => $date,
            'alasan' => 'pemakaian',
            'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        ], $this->staff);

        $secondOut = $this->stockService->createStockOut([
            'tanggal' => $date,
            'alasan' => 'pemakaian',
            'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        ], $this->staff);

        $this->assertSame("BK-{$dateCode}-001", $firstOut->no_transaksi);
        $this->assertSame("BK-{$dateCode}-002", $secondOut->no_transaksi);

        // BR-06: Transaksi tidak memiliki route edit, update, atau destroy
        $this->actingAs($this->staff)->delete("/stock-ins/{$firstIn->id}")->assertStatus(405);
        $this->actingAs($this->staff)->put("/stock-ins/{$firstIn->id}")->assertStatus(405);
        $this->actingAs($this->staff)->delete("/stock-outs/{$firstOut->id}")->assertStatus(405);
        $this->actingAs($this->staff)->put("/stock-outs/{$firstOut->id}")->assertStatus(405);

        // Pimpinan hanya bisa membaca daftar & detail transaksi tanpa tombol tambah
        $this->actingAs($this->pimpinan)
            ->get(route('stock-ins.index'))
            ->assertOk()
            ->assertDontSee('Tambah Barang Masuk');

        $this->actingAs($this->pimpinan)
            ->get(route('stock-ins.show', $firstIn))
            ->assertOk()
            ->assertSee($firstIn->no_transaksi)
            ->assertDontSee('Catat Barang Masuk');

        $this->actingAs($this->pimpinan)
            ->get(route('stock-outs.index'))
            ->assertOk()
            ->assertDontSee('Tambah Barang Keluar');

        $this->actingAs($this->pimpinan)
            ->get(route('stock-outs.show', $firstOut))
            ->assertOk()
            ->assertSee($firstOut->no_transaksi)
            ->assertDontSee('Catat Barang Keluar');

        $this->actingAs($this->pimpinan)
            ->get(route('stock-movements.index', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee($firstIn->no_transaksi);
    }

    public function test_tc12_tc13_seeder_stock_consistency_and_movement_chain(): void
    {
        // TC-13: Kueri konsistensi PRD §12.6 harus mengembalikan 0 baris
        $inconsistentRows = DB::select("
            SELECT p.id, p.kode_barang, p.stok,
                   COALESCE(SUM(CASE WHEN m.tipe='in' THEN m.jumlah
                                     WHEN m.tipe='out' THEN -m.jumlah ELSE 0 END), 0) AS dari_kartu
            FROM products p
            LEFT JOIN stock_movements m ON m.product_id = p.id
            GROUP BY p.id, p.kode_barang, p.stok
            HAVING p.stok <> dari_kartu
        ");

        $this->assertCount(0, $inconsistentRows, 'Terdapat ketidakcocokan antara kolom products.stok dan penjumlahan stock_movements.');

        // Pastikan data seeder memiliki variasi status stok: aman, menipis, dan habis
        $this->assertGreaterThan(0, Product::outOfStock()->count());
        $this->assertGreaterThan(0, Product::lowStock()->where('stok', '>', 0)->count());
        $this->assertGreaterThan(0, Product::whereColumn('stok', '>', 'stok_minimum')->count());

        // TC-12: Untuk setiap barang, rantai stok_sebelum dan stok_sesudah pada stock_movements berkesinambungan dari 0 hingga stok akhir
        $products = Product::with(['stockMovements' => fn ($q) => $q->orderBy('id')])->get();

        foreach ($products as $product) {
            $runningStock = 0;
            foreach ($product->stockMovements as $movement) {
                $this->assertSame($runningStock, $movement->stok_sebelum, "stok_sebelum tidak cocok pada barang {$product->kode_barang}");
                $runningStock = $movement->tipe === 'in'
                    ? $runningStock + $movement->jumlah
                    : $runningStock - $movement->jumlah;
                $this->assertSame($runningStock, $movement->stok_sesudah, "stok_sesudah tidak cocok pada barang {$product->kode_barang}");
            }
            $this->assertSame($runningStock, $product->stok, "Stok akhir tidak cocok pada barang {$product->kode_barang}");
        }
    }
}
