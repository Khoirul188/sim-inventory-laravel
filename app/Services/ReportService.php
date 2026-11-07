<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReportService
{
    public const VALID_TYPES = ['stok', 'masuk', 'keluar', 'minimum', 'kartu-stok'];

    /**
     * Periksa apakah jenis laporan valid.
     */
    public function isValidType(string $jenis): bool
    {
        return in_array($jenis, self::VALID_TYPES, true);
    }

    /**
     * Validasi filter laporan (khususnya rentang tanggal wajib & maksimal 12 bulan untuk Barang Masuk & Keluar — PRD §15).
     *
     * @throws ValidationException
     */
    public function validateFilters(string $jenis, array $input, bool $isExport = false): array
    {
        $requiresDateRange = in_array($jenis, ['masuk', 'keluar'], true);

        // Pada pratinjau layar pertama kali tanpa query string, isi default bulan berjalan agar langsung tampil
        if ($requiresDateRange && ! $isExport && $input === []) {
            $input['dari'] = now()->startOfMonth()->toDateString();
            $input['sampai'] = now()->toDateString();
        }

        $rules = match ($jenis) {
            'stok' => [
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
                'status' => ['nullable', 'in:aman,menipis,habis'],
            ],
            'masuk' => [
                'dari' => ['required', 'date'],
                'sampai' => ['required', 'date', 'after_or_equal:dari'],
                'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            ],
            'keluar' => [
                'dari' => ['required', 'date'],
                'sampai' => ['required', 'date', 'after_or_equal:dari'],
                'alasan' => ['nullable', 'in:pemakaian,penjualan,rusak,koreksi,lainnya'],
            ],
            'minimum' => [
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            ],
            'kartu-stok' => [
                'product_id' => ['nullable', 'integer', 'exists:products,id'],
                'tipe' => ['nullable', 'in:in,out'],
                'dari' => ['nullable', 'date'],
                'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            ],
            default => [],
        };

        $messages = [
            'dari.required' => 'Tanggal mulai wajib diisi untuk laporan ini.',
            'dari.date' => 'Format tanggal mulai tidak valid.',
            'sampai.required' => 'Tanggal selesai wajib diisi untuk laporan ini.',
            'sampai.date' => 'Format tanggal selesai tidak valid.',
            'sampai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ];

        $validator = Validator::make($input, $rules, $messages);

        // Cek batas rentang maksimal 12 bulan (PRD §15, TC-66)
        $validator->after(function ($v) use ($input) {
            if (! empty($input['dari']) && ! empty($input['sampai']) && ! $v->errors()->has('dari') && ! $v->errors()->has('sampai')) {
                $start = Carbon::parse($input['dari'])->startOfDay();
                $end = Carbon::parse($input['sampai'])->startOfDay();

                if ($end->gt($start->copy()->addMonthsNoOverflow(12))) {
                    $v->errors()->add('sampai', 'Rentang tanggal laporan maksimal 12 bulan.');
                }
            }
        });

        $validated = $validator->validate();

        return [
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'status' => $validated['status'] ?? null,
            'supplier_id' => isset($validated['supplier_id']) ? (int) $validated['supplier_id'] : null,
            'alasan' => $validated['alasan'] ?? null,
            'product_id' => isset($validated['product_id']) ? (int) $validated['product_id'] : null,
            'tipe' => $validated['tipe'] ?? null,
            'dari' => $validated['dari'] ?? null,
            'sampai' => $validated['sampai'] ?? null,
        ];
    }

    /**
     * Ambil seluruh data pratinjau laporan di layar (dengan paginasi, ringkasan, dan opsi dropdown filter).
     */
    public function getPreviewData(string $jenis, array $filters, int $perPage = 15): array
    {
        $query = $this->buildQuery($jenis, $filters);
        $rows = $query->paginate($perPage)->withQueryString();
        $summary = $this->getSummary($jenis, $filters);
        $meta = $this->getReportMeta($jenis, $filters);

        return [
            'jenis' => $jenis,
            'filters' => $filters,
            'rows' => $rows,
            'summary' => $summary,
            'meta' => $meta,
            'categories' => Category::orderBy('nama')->get(),
            'suppliers' => Supplier::orderBy('nama')->get(),
            'products' => Product::withTrashed()->orderBy('kode_barang')->get(),
            'alasanLabels' => StockOut::alasanLabels(),
        ];
    }

    /**
     * Ambil seluruh baris dan ringkasan untuk ekspor PDF / Excel (tanpa paginasi).
     */
    public function getExportData(string $jenis, array $filters): array
    {
        $rows = $this->buildQuery($jenis, $filters)->get();
        $summary = $this->getSummary($jenis, $filters);
        $meta = $this->getReportMeta($jenis, $filters);

        return [
            'jenis' => $jenis,
            'filters' => $filters,
            'rows' => $rows,
            'summary' => $summary,
            'meta' => $meta,
        ];
    }

    /**
     * Bangun query utama untuk masing-masing laporan dengan pengurutan sesuai PRD §15.
     */
    public function buildQuery(string $jenis, array $filters): Builder
    {
        return match ($jenis) {
            'stok' => $this->buildCurrentStockQuery($filters),
            'masuk' => $this->buildStockInQuery($filters),
            'keluar' => $this->buildStockOutQuery($filters),
            'minimum' => $this->buildMinimumStockQuery($filters),
            'kartu-stok' => $this->buildStockCardQuery($filters),
        };
    }

    /**
     * 1. Query Laporan Stok Saat Ini: urut menurut kategori lalu nama barang (PRD §15).
     * Barang yang di-soft delete tidak dihitung dalam stok aktif saat ini.
     */
    protected function buildCurrentStockQuery(array $filters): Builder
    {
        $query = Product::query()
            ->with(['category', 'unit'])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*');

        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'aman' => $query->whereColumn('products.stok', '>', 'products.stok_minimum')->where('products.stok', '>', 0),
                'menipis' => $query->whereColumn('products.stok', '<=', 'products.stok_minimum'),
                'habis' => $query->where('products.stok', 0),
                default => null,
            };
        }

        return $query->orderBy('categories.nama')
            ->orderBy('products.nama')
            ->orderBy('products.id');
    }

    /**
     * 2. Query Laporan Barang Masuk: urut menurut tanggal lalu nomor transaksi (PRD §15).
     * Barang yang di-soft delete tetap ditampilkan pada laporan transaksi historis.
     */
    protected function buildStockInQuery(array $filters): Builder
    {
        $query = StockInItem::query()
            ->with(['stockIn.supplier', 'product.unit'])
            ->join('stock_ins', 'stock_in_items.stock_in_id', '=', 'stock_ins.id')
            ->select('stock_in_items.*');

        if (! empty($filters['dari'])) {
            $query->whereDate('stock_ins.tanggal', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('stock_ins.tanggal', '<=', $filters['sampai']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('stock_ins.supplier_id', $filters['supplier_id']);
        }

        return $query->orderBy('stock_ins.tanggal')
            ->orderBy('stock_ins.no_transaksi')
            ->orderBy('stock_in_items.id');
    }

    /**
     * 3. Query Laporan Barang Keluar: urut menurut tanggal lalu nomor transaksi (PRD §15).
     * Barang yang di-soft delete tetap ditampilkan pada laporan transaksi historis.
     */
    protected function buildStockOutQuery(array $filters): Builder
    {
        $query = StockOutItem::query()
            ->with(['stockOut', 'product.unit'])
            ->join('stock_outs', 'stock_out_items.stock_out_id', '=', 'stock_outs.id')
            ->select('stock_out_items.*');

        if (! empty($filters['dari'])) {
            $query->whereDate('stock_outs.tanggal', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('stock_outs.tanggal', '<=', $filters['sampai']);
        }

        if (! empty($filters['alasan'])) {
            $query->where('stock_outs.alasan', $filters['alasan']);
        }

        return $query->orderBy('stock_outs.tanggal')
            ->orderBy('stock_outs.no_transaksi')
            ->orderBy('stock_out_items.id');
    }

    /**
     * 4. Query Laporan Barang Stok Minimum: urut menurut rasio stok / stok_minimum terkecil (PRD §15).
     */
    protected function buildMinimumStockQuery(array $filters): Builder
    {
        $query = Product::query()
            ->with(['category', 'unit'])
            ->lowStock();

        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        return $query->orderByRaw('CASE WHEN stok = 0 THEN 0 ELSE 1 END ASC')
            ->orderByRaw('(stok * 1.0 / NULLIF(stok_minimum, 0)) ASC')
            ->orderBy('stok')
            ->orderBy('nama')
            ->orderBy('id');
    }

    /**
     * 5. Query Laporan Kartu Stok (opsional — PRD §15 & AGENTS.md §7).
     */
    protected function buildStockCardQuery(array $filters): Builder
    {
        $query = StockMovement::query()
            ->with(['product.unit', 'user', 'stockIn', 'stockOut']);

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (! empty($filters['dari'])) {
            $query->whereDate('created_at', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('created_at', '<=', $filters['sampai']);
        }

        return $query->orderBy('created_at')->orderBy('id');
    }

    /**
     * Hitung ringkasan laporan sesuai spesifikasi tabel PRD §15.
     */
    public function getSummary(string $jenis, array $filters): array
    {
        return match ($jenis) {
            'stok' => $this->getCurrentStockSummary($filters),
            'masuk' => $this->getStockInSummary($filters),
            'keluar' => $this->getStockOutSummary($filters),
            'minimum' => $this->getMinimumStockSummary($filters),
            'kartu-stok' => $this->getStockCardSummary($filters),
        };
    }

    protected function getCurrentStockSummary(array $filters): array
    {
        $baseQuery = Product::query();

        if (! empty($filters['category_id'])) {
            $baseQuery->where('products.category_id', $filters['category_id']);
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'aman' => $baseQuery->whereColumn('products.stok', '>', 'products.stok_minimum')->where('products.stok', '>', 0),
                'menipis' => $baseQuery->whereColumn('products.stok', '<=', 'products.stok_minimum'),
                'habis' => $baseQuery->where('products.stok', 0),
                default => null,
            };
        }

        $totalJenisBarang = (clone $baseQuery)->count();
        $totalStok = (int) (clone $baseQuery)->sum('products.stok');

        $subtotalPerKategori = (clone $baseQuery)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.nama as kategori',
                DB::raw('COUNT(products.id) as jumlah_jenis'),
                DB::raw('COALESCE(SUM(products.stok), 0) as total_stok')
            )
            ->groupBy('categories.id', 'categories.nama')
            ->orderBy('categories.nama')
            ->get()
            ->map(fn ($row) => [
                'kategori' => $row->kategori,
                'jumlah_jenis' => (int) $row->jumlah_jenis,
                'total_stok' => (int) $row->total_stok,
            ])
            ->values()
            ->all();

        return [
            'totalJenisBarang' => $totalJenisBarang,
            'totalStok' => $totalStok,
            'subtotalPerKategori' => $subtotalPerKategori,
        ];
    }

    protected function getStockInSummary(array $filters): array
    {
        $query = StockInItem::query()
            ->join('stock_ins', 'stock_in_items.stock_in_id', '=', 'stock_ins.id');

        if (! empty($filters['dari'])) {
            $query->whereDate('stock_ins.tanggal', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('stock_ins.tanggal', '<=', $filters['sampai']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('stock_ins.supplier_id', $filters['supplier_id']);
        }

        $totals = $query->selectRaw('
            COUNT(stock_in_items.id) as total_baris,
            COALESCE(SUM(stock_in_items.jumlah), 0) as total_jumlah,
            COALESCE(SUM(stock_in_items.jumlah * stock_in_items.harga_beli), 0) as total_nilai
        ')->first();

        return [
            'totalBaris' => (int) ($totals->total_baris ?? 0),
            'totalJumlah' => (int) ($totals->total_jumlah ?? 0),
            'totalNilai' => (float) ($totals->total_nilai ?? 0),
        ];
    }

    protected function getStockOutSummary(array $filters): array
    {
        $query = StockOutItem::query()
            ->join('stock_outs', 'stock_out_items.stock_out_id', '=', 'stock_outs.id');

        if (! empty($filters['dari'])) {
            $query->whereDate('stock_outs.tanggal', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('stock_outs.tanggal', '<=', $filters['sampai']);
        }

        if (! empty($filters['alasan'])) {
            $query->where('stock_outs.alasan', $filters['alasan']);
        }

        $totals = $query->selectRaw('
            COUNT(stock_out_items.id) as total_baris,
            COALESCE(SUM(stock_out_items.jumlah), 0) as total_jumlah
        ')->first();

        return [
            'totalBaris' => (int) ($totals->total_baris ?? 0),
            'totalJumlah' => (int) ($totals->total_jumlah ?? 0),
        ];
    }

    protected function getMinimumStockSummary(array $filters): array
    {
        $baseQuery = Product::query()->lowStock();

        if (! empty($filters['category_id'])) {
            $baseQuery->where('products.category_id', $filters['category_id']);
        }

        $items = $baseQuery->get(['stok', 'stok_minimum']);

        // Jumlah barang menipis mencakup stok <= stok_minimum; habis adalah stok = 0 (PRD §11.4 & §15)
        $jumlahMenipis = $items->count();
        $jumlahHabis = $items->where('stok', 0)->count();
        $totalKekurangan = (int) $items->sum(fn ($p) => self::calculateShortage((int) $p->stok, (int) $p->stok_minimum));

        return [
            'jumlahMenipis' => $jumlahMenipis,
            'jumlahHabis' => $jumlahHabis,
            'totalKekurangan' => $totalKekurangan,
        ];
    }

    protected function getStockCardSummary(array $filters): array
    {
        $baseQuery = $this->buildStockCardQuery($filters)->reorder();

        $totalBaris = (clone $baseQuery)->count();
        $totalMasuk = (int) (clone $baseQuery)->where('tipe', 'in')->sum('jumlah');
        $totalKeluar = (int) (clone $baseQuery)->where('tipe', 'out')->sum('jumlah');

        return [
            'totalBaris' => $totalBaris,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
        ];
    }

    /**
     * Rumus kekurangan stok minimum: max(stok_minimum - stok, 0) (PRD §15).
     */
    public static function calculateShortage(int $stok, int $stokMinimum): int
    {
        return max($stokMinimum - $stok, 0);
    }

    /**
     * Metadata laporan (judul, orientasi kertas PDF, dan deskripsi filter/periode untuk kop PDF).
     */
    public function getReportMeta(string $jenis, array $filters): array
    {
        $titles = [
            'stok' => 'Laporan Stok Saat Ini',
            'masuk' => 'Laporan Barang Masuk',
            'keluar' => 'Laporan Barang Keluar',
            'minimum' => 'Laporan Barang Stok Minimum',
            'kartu-stok' => 'Laporan Kartu Stok',
        ];

        $orientations = [
            'stok' => 'portrait',
            'masuk' => 'landscape',
            'keluar' => 'landscape',
            'minimum' => 'portrait',
            'kartu-stok' => 'landscape',
        ];

        $filterParts = [];

        if (! empty($filters['dari']) && ! empty($filters['sampai'])) {
            $dariLabel = Carbon::parse($filters['dari'])->translatedFormat('d M Y');
            $sampaiLabel = Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
            $filterParts[] = "Periode: {$dariLabel} s/d {$sampaiLabel}";
        } elseif (! empty($filters['dari'])) {
            $filterParts[] = 'Mulai: '.Carbon::parse($filters['dari'])->translatedFormat('d M Y');
        } elseif (! empty($filters['sampai'])) {
            $filterParts[] = 'Sampai: '.Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
        }

        if (! empty($filters['category_id'])) {
            $catName = Category::find($filters['category_id'])?->nama ?? '—';
            $filterParts[] = "Kategori: {$catName}";
        } elseif (in_array($jenis, ['stok', 'minimum'], true)) {
            $filterParts[] = 'Kategori: Semua Kategori';
        }

        if (! empty($filters['status'])) {
            $statusLabel = match ($filters['status']) {
                'aman' => 'Aman',
                'menipis' => 'Menipis',
                'habis' => 'Habis',
                default => ucfirst($filters['status']),
            };
            $filterParts[] = "Status Stok: {$statusLabel}";
        } elseif ($jenis === 'stok') {
            $filterParts[] = 'Status Stok: Semua Status';
        }

        if (! empty($filters['supplier_id'])) {
            $supName = Supplier::find($filters['supplier_id'])?->nama ?? '—';
            $filterParts[] = "Supplier: {$supName}";
        } elseif ($jenis === 'masuk') {
            $filterParts[] = 'Supplier: Semua Supplier';
        }

        if (! empty($filters['alasan'])) {
            $alasanLabel = StockOut::alasanLabels()[$filters['alasan']] ?? ucfirst($filters['alasan']);
            $filterParts[] = "Alasan: {$alasanLabel}";
        } elseif ($jenis === 'keluar') {
            $filterParts[] = 'Alasan: Semua Alasan';
        }

        if (! empty($filters['product_id'])) {
            $prod = Product::withTrashed()->find($filters['product_id']);
            $filterParts[] = $prod ? "Barang: {$prod->kode_barang} — {$prod->nama}" : 'Barang: —';
        } elseif ($jenis === 'kartu-stok') {
            $filterParts[] = 'Barang: Semua Barang';
        }

        return [
            'title' => $titles[$jenis] ?? 'Laporan Inventory',
            'orientation' => $orientations[$jenis] ?? 'portrait',
            'filterSummary' => implode(' · ', $filterParts),
            'printedAt' => now()->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i').' WIB',
        ];
    }
}
