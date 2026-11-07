<?php

namespace Tests\Feature;

use App\Exports\BaseReportExport;
use App\Exports\CurrentStockExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ReportService;
use App\Services\StockService;
use Carbon\Carbon;
use Database\Seeders\RoleAndUserSeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Ekstrak teks dari stream FlateDecode di dalam biner PDF yang dihasilkan DomPDF.
     */
    protected function extractPdfStreamText(string $pdfBinary): string
    {
        $decodedText = $pdfBinary;

        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfBinary, $matches)) {
            foreach ($matches[1] as $stream) {
                $inflated = @gzuncompress($stream);
                if ($inflated !== false) {
                    $decodedText .= "\n".$inflated;
                }
            }
        }

        return $decodedText;
    }

    public function test_role_access_and_preview_html_contains_pdf_and_excel_links_with_active_filters(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 7, 10, 0, 0, 'Asia/Jakarta'));

        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();

        $unit = Unit::firstOrFail();
        $category = Category::create(['nama' => 'Kategori Kertas']);
        $supplier = Supplier::create(['kode_supplier' => 'SUP-01', 'nama' => 'PT Kertas Jaya', 'telepon' => '021-123']);
        $product = Product::create([
            'kode_barang' => 'BRG-0001',
            'nama' => 'Kertas HVS A4',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'harga_beli' => 50000,
            'stok' => 0,
            'stok_minimum' => 10,
        ]);

        // Tamu dialihkan ke login
        $this->get(route('reports.show', 'stok'))->assertRedirect(route('login'));

        // Staff ditolak (403) pada semua laporan dan unduhan PDF/Excel (TC-33)
        foreach (['stok', 'masuk', 'keluar', 'minimum', 'kartu-stok'] as $jenis) {
            $this->actingAs($staff)->get(route('reports.show', $jenis))->assertForbidden();
            $this->actingAs($staff)->get(route('reports.pdf', $jenis))->assertForbidden();
            $this->actingAs($staff)->get(route('reports.excel', $jenis))->assertForbidden();
        }

        // Staff dapat membuka halaman Kartu Stok (/stock-movements), tetapi TIDAK melihat tombol Unduh PDF / Unduh Excel
        $this->actingAs($staff)
            ->get(route('stock-movements.index'))
            ->assertOk()
            ->assertDontSee('Unduh PDF')
            ->assertDontSee('Unduh Excel');

        // Skenario filter aktif untuk 4 laporan + halaman Kartu Stok
        $reportCases = [
            'stok' => [
                'category_id' => $category->id,
                'status' => 'habis',
            ],
            'masuk' => [
                'dari' => '2026-11-01',
                'sampai' => '2026-11-07',
                'supplier_id' => $supplier->id,
            ],
            'keluar' => [
                'dari' => '2026-11-01',
                'sampai' => '2026-11-07',
                'alasan' => 'pemakaian',
            ],
            'minimum' => [
                'category_id' => $category->id,
            ],
        ];

        foreach ([$admin, $pimpinan] as $allowedUser) {
            foreach ($reportCases as $jenis => $filterQuery) {
                $params = array_merge(['jenis' => $jenis], $filterQuery);
                $expectedPdfUrl = route('reports.pdf', $params);
                $expectedExcelUrl = route('reports.excel', $params);

                $response = $this->actingAs($allowedUser)->get(route('reports.show', $params));
                $response->assertOk();
                $response->assertSee('Unduh PDF');
                $response->assertSee('Unduh Excel');
                $response->assertSee('href="'.e($expectedPdfUrl).'"', false);
                $response->assertSee('href="'.e($expectedExcelUrl).'"', false);
            }

            // Halaman Kartu Stok (/stock-movements) juga menampilkan tombol Unduh PDF dan Unduh Excel dengan filter aktif
            $movementFilter = [
                'product_id' => $product->id,
                'tipe' => 'in',
                'dari' => '2026-11-01',
                'sampai' => '2026-11-07',
            ];
            $expectedCardPdfUrl = route('reports.pdf', array_merge(['jenis' => 'kartu-stok'], $movementFilter));
            $expectedCardExcelUrl = route('reports.excel', array_merge(['jenis' => 'kartu-stok'], $movementFilter));

            $cardResponse = $this->actingAs($allowedUser)->get(route('stock-movements.index', $movementFilter));
            $cardResponse->assertOk();
            $cardResponse->assertSee('Unduh PDF');
            $cardResponse->assertSee('Unduh Excel');
            $cardResponse->assertSee('href="'.e($expectedCardPdfUrl).'"', false);
            $cardResponse->assertSee('href="'.e($expectedCardExcelUrl).'"', false);
        }

        // Jenis laporan tidak dikenal -> 404
        $this->actingAs($admin)->get('/reports/tidak-ada')->assertNotFound();
    }

    public function test_date_range_validation_required_and_max_12_months_on_masuk_and_keluar(): void
    {
        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();
        $this->actingAs($pimpinan);

        // 1. Tanggal kosong saat mengirim filter pada Barang Masuk & Keluar ditolak (bukan 500)
        $this->get(route('reports.show', ['jenis' => 'masuk', 'dari' => '', 'sampai' => '']))
            ->assertSessionHasErrors(['dari', 'sampai']);

        $this->get(route('reports.pdf', ['jenis' => 'masuk']))
            ->assertSessionHasErrors(['dari', 'sampai']);

        $this->get(route('reports.excel', ['jenis' => 'keluar']))
            ->assertSessionHasErrors(['dari', 'sampai']);

        // 2. Tanggal selesai sebelum tanggal mulai ditolak
        $this->get(route('reports.show', [
            'jenis' => 'masuk',
            'dari' => '2026-10-15',
            'sampai' => '2026-10-01',
        ]))->assertSessionHasErrors(['sampai']);

        // 3. Rentang tanggal > 12 bulan ditolak dengan pesan jelas (TC-66)
        $response = $this->from(route('reports.show', 'stok'))
            ->get(route('reports.pdf', [
                'jenis' => 'masuk',
                'dari' => '2025-01-01',
                'sampai' => '2026-01-02',
            ]));

        $response->assertRedirect(route('reports.show', 'stok'));
        $response->assertSessionHasErrors([
            'sampai' => 'Rentang tanggal laporan maksimal 12 bulan.',
        ]);

        // Rentang tepat <= 12 bulan diterima
        $this->get(route('reports.show', [
            'jenis' => 'masuk',
            'dari' => '2025-11-08',
            'sampai' => '2026-11-07',
        ]))->assertOk();
    }

    public function test_four_reports_content_filters_soft_delete_shortage_and_tc10_sequential_numbers(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 7, 10, 0, 0, 'Asia/Jakarta'));

        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $this->actingAs($admin);

        $unit = Unit::firstOrFail();
        $supA = Supplier::create(['kode_supplier' => 'SUP-A', 'nama' => 'PT Sumber Kertas', 'telepon' => '021-111']);
        $supB = Supplier::create(['kode_supplier' => 'SUP-B', 'nama' => 'CV Jaya Elektronik', 'telepon' => '021-222']);

        $catAtk = Category::create(['nama' => 'Alat Tulis']);
        $catKertas = Category::create(['nama' => 'Kertas']);

        // Barang 1: Kategori Kertas, stok akhir = 25, min = 10 -> Aman
        $p1 = Product::create([
            'kode_barang' => 'BRG-0001',
            'nama' => 'Kertas A4',
            'category_id' => $catKertas->id,
            'unit_id' => $unit->id,
            'harga_beli' => 50000,
            'stok' => 0,
            'stok_minimum' => 10,
            'lokasi_rak' => 'A-01',
        ]);

        // Barang 2: Kategori Alat Tulis, stok akhir = 4, min = 10 -> Menipis (kekurangan = 6, rasio = 0.4)
        $p2 = Product::create([
            'kode_barang' => 'BRG-0002',
            'nama' => 'Pulpen Hitam',
            'category_id' => $catAtk->id,
            'unit_id' => $unit->id,
            'harga_beli' => 25000,
            'stok' => 0,
            'stok_minimum' => 10,
            'lokasi_rak' => 'B-01',
        ]);

        // Barang 3: Kategori Alat Tulis, stok akhir = 0, min = 8 -> Habis (kekurangan = 8, rasio = 0.0)
        $p3 = Product::create([
            'kode_barang' => 'BRG-0003',
            'nama' => 'Spidol Papan',
            'category_id' => $catAtk->id,
            'unit_id' => $unit->id,
            'harga_beli' => 15000,
            'stok' => 0,
            'stok_minimum' => 8,
            'lokasi_rak' => 'B-02',
        ]);

        // Barang 4: akan di-soft delete setelah punya transaksi masuk & keluar
        $p4 = Product::create([
            'kode_barang' => 'BRG-0004',
            'nama' => 'Barang Lama Dihapus',
            'category_id' => $catKertas->id,
            'unit_id' => $unit->id,
            'harga_beli' => 40000,
            'stok' => 0,
            'stok_minimum' => 5,
            'lokasi_rak' => 'A-09',
        ]);

        $stockService = app(StockService::class);

        // TC-10: Dua transaksi pada hari yang sama menghasilkan nomor berurutan -001 dan -002
        $in1 = $stockService->createStockIn([
            'tanggal' => '2026-11-02',
            'supplier_id' => $supA->id,
            'keterangan' => 'Masuk pertama',
            'items' => [
                ['product_id' => $p1->id, 'jumlah' => 30, 'harga_beli' => 50000],
                ['product_id' => $p4->id, 'jumlah' => 10, 'harga_beli' => 40000],
            ],
        ], $admin);

        $in2 = $stockService->createStockIn([
            'tanggal' => '2026-11-02',
            'supplier_id' => $supB->id,
            'keterangan' => 'Masuk kedua hari sama',
            'items' => [
                ['product_id' => $p2->id, 'jumlah' => 10, 'harga_beli' => 25000],
            ],
        ], $admin);

        $this->assertSame('BM-20261102-001', $in1->no_transaksi);
        $this->assertSame('BM-20261102-002', $in2->no_transaksi);

        $out1 = $stockService->createStockOut([
            'tanggal' => '2026-11-05',
            'alasan' => 'pemakaian',
            'tujuan' => 'Bagian Umum',
            'items' => [
                ['product_id' => $p1->id, 'jumlah' => 5],
                ['product_id' => $p2->id, 'jumlah' => 6],
            ],
        ], $admin);

        $out2 = $stockService->createStockOut([
            'tanggal' => '2026-11-05',
            'alasan' => 'rusak',
            'tujuan' => null,
            'keterangan' => 'Kemasan koyak',
            'items' => [
                ['product_id' => $p4->id, 'jumlah' => 3],
            ],
        ], $admin);

        $this->assertSame('BK-20261105-001', $out1->no_transaksi);
        $this->assertSame('BK-20261105-002', $out2->no_transaksi);

        // Soft delete P4
        $p4->delete();

        $reportService = app(ReportService::class);

        // 1. Laporan Stok Saat Ini: P4 (soft-deleted) tidak ikut; urut menurut kategori (Alat Tulis -> Kertas) lalu nama
        $stokData = $reportService->getExportData('stok', []);
        $this->assertCount(3, $stokData['rows']);
        $this->assertSame(['BRG-0002', 'BRG-0003', 'BRG-0001'], $stokData['rows']->pluck('kode_barang')->all());
        $this->assertSame(3, $stokData['summary']['totalJenisBarang']);
        $this->assertSame(29, $stokData['summary']['totalStok']); // 25 + 4 + 0

        // Filter Stok Saat Ini berdasarkan kategori & status
        $stokMenipis = $reportService->getExportData('stok', ['status' => 'menipis']);
        $this->assertCount(2, $stokMenipis['rows']); // P2 (4/10) dan P3 (0/8)

        // 2. Laporan Barang Masuk: P4 yang sudah di-soft delete TETAP muncul di laporan transaksi historis
        $masukData = $reportService->getExportData('masuk', [
            'dari' => '2026-11-01',
            'sampai' => '2026-11-07',
        ]);
        $this->assertCount(3, $masukData['rows']);
        $this->assertTrue($masukData['rows']->contains(fn ($r) => $r->product_id === $p4->id));
        $this->assertSame(50, $masukData['summary']['totalJumlah']); // 30 + 10 + 10
        $this->assertSame(2150000.0, $masukData['summary']['totalNilai']); // (30*50000) + (10*40000) + (10*25000)

        // Filter Barang Masuk per supplier (TC-65)
        $masukSupB = $reportService->getExportData('masuk', [
            'dari' => '2026-11-01',
            'sampai' => '2026-11-07',
            'supplier_id' => $supB->id,
        ]);
        $this->assertCount(1, $masukSupB['rows']);
        $this->assertSame(10, $masukSupB['summary']['totalJumlah']);
        $this->assertSame(250000.0, $masukSupB['summary']['totalNilai']);

        // 3. Laporan Barang Keluar: P4 yang di-soft delete TETAP muncul; filter alasan berfungsi
        $keluarData = $reportService->getExportData('keluar', [
            'dari' => '2026-11-01',
            'sampai' => '2026-11-07',
        ]);
        $this->assertCount(3, $keluarData['rows']);
        $this->assertTrue($keluarData['rows']->contains(fn ($r) => $r->product_id === $p4->id));
        $this->assertSame(14, $keluarData['summary']['totalJumlah']); // 5 + 6 + 3

        $keluarRusak = $reportService->getExportData('keluar', [
            'dari' => '2026-11-01',
            'sampai' => '2026-11-07',
            'alasan' => 'rusak',
        ]);
        $this->assertCount(1, $keluarRusak['rows']);
        $this->assertSame(3, $keluarRusak['summary']['totalJumlah']);

        // 4. Laporan Stok Minimum: urut rasio terkecil (P3 stok 0 di atas P2 stok 4), kekurangan = max(min - stok, 0)
        $minData = $reportService->getExportData('minimum', []);
        $this->assertCount(2, $minData['rows']);
        $this->assertSame($p3->id, $minData['rows'][0]->id);
        $this->assertSame($p2->id, $minData['rows'][1]->id);
        $p2->refresh();
        $p3->refresh();
        $this->assertSame(8, ReportService::calculateShortage($p3->stok, $p3->stok_minimum));
        $this->assertSame(6, ReportService::calculateShortage($p2->stok, $p2->stok_minimum));
        $this->assertSame(2, $minData['summary']['jumlahMenipis']);
        $this->assertSame(1, $minData['summary']['jumlahHabis']);
        $this->assertSame(14, $minData['summary']['totalKekurangan']);
    }

    public function test_pdf_and_excel_downloads_for_all_reports_and_formula_injection_protection(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 7, 14, 30, 0, 'Asia/Jakarta'));

        $this->seed([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);

        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $this->actingAs($admin);

        $unit = Unit::firstOrFail();
        $category = Category::create(['nama' => 'Kategori Uji']);
        $supplier = Supplier::create(['kode_supplier' => 'SUP-UJI', 'nama' => 'PT Pemasok Uji', 'telepon' => '021-999']);

        // Buat barang dengan nama berbahaya untuk menguji proteksi injeksi rumus Excel (TC-38)
        $product = Product::create([
            'kode_barang' => 'BRG-0001',
            'nama' => '=HYPERLINK("http://evil.test","Klik")',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'harga_beli' => 45000,
            'stok' => 0,
            'stok_minimum' => 100,
            'lokasi_rak' => '+RAK-01',
        ]);

        $stockService = app(StockService::class);
        $stockService->createStockIn([
            'tanggal' => '2026-11-03',
            'supplier_id' => $supplier->id,
            'keterangan' => 'Masuk uji ekspor',
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 25, 'harga_beli' => 45000],
            ],
        ], $admin);

        $stockService->createStockOut([
            'tanggal' => '2026-11-05',
            'alasan' => 'pemakaian',
            'tujuan' => 'Divisi Uji',
            'items' => [
                ['product_id' => $product->id, 'jumlah' => 5],
            ],
        ], $admin);

        // 1. Uji unduhan PDF & Excel untuk keempat laporan + Kartu Stok (TC-64)
        $downloads = [
            'stok' => ['params' => [], 'qtyCol' => 'F', 'expectedQty' => 20],
            'masuk' => ['params' => ['dari' => '2026-11-01', 'sampai' => '2026-11-07'], 'qtyCol' => 'G', 'expectedQty' => 25],
            'keluar' => ['params' => ['dari' => '2026-11-01', 'sampai' => '2026-11-07'], 'qtyCol' => 'H', 'expectedQty' => 5],
            'minimum' => ['params' => [], 'qtyCol' => 'E', 'expectedQty' => 20],
            'kartu-stok' => ['params' => ['dari' => '2026-11-01', 'sampai' => '2026-11-07'], 'qtyCol' => 'G', 'expectedQty' => 25],
        ];

        foreach ($downloads as $jenis => $cfg) {
            $routeParams = array_merge(['jenis' => $jenis], $cfg['params']);

            // Unduh PDF
            $pdfResponse = $this->get(route('reports.pdf', $routeParams));
            $pdfResponse->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('Content-Type'));
            $this->assertStringContainsString("laporan-{$jenis}-20261107.pdf", (string) $pdfResponse->headers->get('Content-Disposition'));

            $pdfBinary = $pdfResponse->getContent();
            $this->assertStringStartsWith('%PDF-', $pdfBinary);

            // Verifikasi stream PDF memuat kop, periode/filter (tanpa pengulangan "Periode: Periode:"), nama pencetak, dan nomor halaman
            $pdfText = $this->extractPdfStreamText($pdfBinary);
            $this->assertStringContainsString('SIM Inventory', $pdfText);
            $this->assertStringContainsString($admin->name, $pdfText);
            $this->assertStringContainsString('Halaman 1 dari 1', $pdfText);
            $this->assertStringNotContainsString('Periode: Periode:', $pdfText);

            // Unduh Excel
            $excelResponse = $this->get(route('reports.excel', $routeParams));
            $excelResponse->assertOk();
            $this->assertStringContainsString(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                (string) $excelResponse->headers->get('Content-Type')
            );
            $this->assertStringContainsString("laporan-{$jenis}-20261107.xlsx", (string) $excelResponse->headers->get('Content-Disposition'));

            // Buka file Excel hasil unduhan dengan PhpSpreadsheet dan pastikan sel Jumlah/Stok bertipe angka (numeric)
            $xlsxPath = $excelResponse->baseResponse->getFile()->getPathname();
            $workbook = IOFactory::load($xlsxPath);
            $sheet = $workbook->getActiveSheet();
            $qtyCell = $sheet->getCell($cfg['qtyCol'].'2');

            $this->assertSame(DataType::TYPE_NUMERIC, $qtyCell->getDataType());
            $this->assertEquals($cfg['expectedQty'], $qtyCell->getValue());

            // Pastikan baris ringkasan di bawah tabel Excel juga bertipe numerik
            if (in_array($jenis, ['stok', 'masuk', 'keluar', 'minimum'], true)) {
                $lastRow = $sheet->getHighestDataRow();
                $summaryCol = $jenis === 'minimum' ? 'G' : $cfg['qtyCol'];
                $summaryCell = $sheet->getCell($summaryCol.$lastRow);
                $this->assertSame(DataType::TYPE_NUMERIC, $summaryCell->getDataType());
            }
        }

        // 2. Uji sanitasi rumus (=, +, -, @) di Excel (TC-38 & PRD §15) serta nilai kosong/strip tunggal tidak diberi tanda kutip
        $this->assertSame("'=SUM(A1:A2)", BaseReportExport::sanitizeText('=SUM(A1:A2)'));
        $this->assertSame("'+12345", BaseReportExport::sanitizeText('+12345'));
        $this->assertSame("'-12345", BaseReportExport::sanitizeText('-12345'));
        $this->assertSame("'@cmd", BaseReportExport::sanitizeText('@cmd'));
        $this->assertSame('', BaseReportExport::sanitizeText(null));
        $this->assertSame('', BaseReportExport::sanitizeText(''));
        $this->assertSame('', BaseReportExport::sanitizeText('-'));
        $this->assertSame('', BaseReportExport::sanitizeText('—'));
        $this->assertSame('Kertas HVS Normal', BaseReportExport::sanitizeText('Kertas HVS Normal'));

        $export = new CurrentStockExport([]);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $cellFormula = $sheet->getCell('C2');
        $export->bindValue($cellFormula, '=HYPERLINK("http://evil.test")');
        $this->assertSame(DataType::TYPE_STRING, $cellFormula->getDataType());
        $this->assertSame('\'=HYPERLINK("http://evil.test")', $cellFormula->getValue());
    }
}
