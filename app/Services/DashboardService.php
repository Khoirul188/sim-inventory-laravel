<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Cache jumlah barang menipis dalam satu siklus request agar tidak terjadi query berulang.
     */
    protected ?int $cachedLowStockCount = null;

    /**
     * Hitung jumlah barang dengan stok menipis (termasuk habis: stok <= stok_minimum).
     * Barang yang di-soft delete otomatis diabaikan oleh global scope SoftDeletes.
     */
    public function getLowStockCount(bool $fresh = false): int
    {
        if (! $fresh && $this->cachedLowStockCount !== null) {
            return $this->cachedLowStockCount;
        }

        return $this->cachedLowStockCount = Product::lowStock()->count();
    }

    /**
     * Kumpulkan seluruh metrik dan koleksi data untuk halaman Dashboard (PRD §11.4 & DESIGN §6).
     */
    public function getDashboardData(string $range = '12m', ?string $search = null): array
    {
        $range = in_array($range, ['12m', '30d'], true) ? $range : '12m';
        $now = now();

        $kpis = $this->getKpiMetrics($now);
        $stockLevel = $this->getStockLevelData($kpis['totalProducts']);
        $this->cachedLowStockCount = $stockLevel['lowStockCount'];

        return [
            'range' => $range,
            'search' => $search,
            'kpis' => $kpis,
            'lowStockCount' => $stockLevel['lowStockCount'],
            'outOfStockCount' => $stockLevel['outOfStockCount'],
            'criticalProducts' => $stockLevel['criticalProducts'],
            'dashboardProducts' => $this->getDashboardProducts($search),
            'movementChart' => $this->getMovementChartData($range, $now),
            'categoryStock' => $this->getCategoryStockData($kpis['totalStock']),
            'recentStockInItems' => $this->getRecentStockInItems(),
            'topOutgoingProducts' => $this->getTopOutgoingProducts($now),
            'recentActivities' => $this->getRecentActivities(),
        ];
    }

    /**
     * Hitung 4 kartu KPI utama beserta badge trennya sesuai rumus PRD §11.4.
     */
    public function getKpiMetrics(?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : now();
        $sevenDaysAgo = $now->copy()->subDays(7);

        // 1. Total jenis barang aktif & barang baru 7 hari terakhir
        $totalProducts = Product::count();
        $newProductsThisWeek = Product::where('created_at', '>=', $sevenDaysAgo)->count();

        // 2. Total stok seluruh barang aktif & selisih bersih (masuk - keluar) 7 hari terakhir
        $totalStock = (int) Product::sum('stok');

        $movements7d = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('stock_movements.created_at', '>=', $sevenDaysAgo)
            ->whereIn('stock_movements.tipe', ['in', 'out'])
            ->selectRaw('stock_movements.tipe, COALESCE(SUM(stock_movements.jumlah), 0) as total')
            ->groupBy('stock_movements.tipe')
            ->pluck('total', 'tipe');

        $in7d = (int) ($movements7d['in'] ?? 0);
        $out7d = (int) ($movements7d['out'] ?? 0);
        $net7Days = $in7d - $out7d;

        // 3 & 4. Barang masuk & keluar bulan ini dibanding bulan lalu pada rentang hari yang sama
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $sameInstantLastMonth = $now->copy()->subMonthNoOverflow();

        $thisMonthTotals = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereBetween('stock_movements.created_at', [$startOfThisMonth, $now])
            ->whereIn('stock_movements.tipe', ['in', 'out'])
            ->selectRaw('stock_movements.tipe, COALESCE(SUM(stock_movements.jumlah), 0) as total')
            ->groupBy('stock_movements.tipe')
            ->pluck('total', 'tipe');

        $lastMonthTotals = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereBetween('stock_movements.created_at', [$startOfLastMonth, $sameInstantLastMonth])
            ->whereIn('stock_movements.tipe', ['in', 'out'])
            ->selectRaw('stock_movements.tipe, COALESCE(SUM(stock_movements.jumlah), 0) as total')
            ->groupBy('stock_movements.tipe')
            ->pluck('total', 'tipe');

        $monthlyIn = (int) ($thisMonthTotals['in'] ?? 0);
        $lastMonthIn = (int) ($lastMonthTotals['in'] ?? 0);
        $inTrend = $this->calculateTrend($monthlyIn, $lastMonthIn);

        $monthlyOut = (int) ($thisMonthTotals['out'] ?? 0);
        $lastMonthOut = (int) ($lastMonthTotals['out'] ?? 0);
        $outTrend = $this->calculateTrend($monthlyOut, $lastMonthOut);

        if ($net7Days > 0) {
            $net7DaysBadge = '▲ +'.format_angka($net7Days);
            $net7DaysVariant = 'tren-naik';
        } elseif ($net7Days < 0) {
            $net7DaysBadge = '▼ −'.format_angka(abs($net7Days));
            $net7DaysVariant = 'tren-turun';
        } else {
            $net7DaysBadge = '0';
            $net7DaysVariant = 'neutral';
        }

        return [
            'totalProducts' => $totalProducts,
            'newProductsThisWeek' => $newProductsThisWeek,
            'newProductsBadge' => '+'.format_angka($newProductsThisWeek),
            'newProductsVariant' => $newProductsThisWeek > 0 ? 'tren-naik' : 'neutral',
            'totalStock' => $totalStock,
            'net7Days' => $net7Days,
            'net7DaysBadge' => $net7DaysBadge,
            'net7DaysVariant' => $net7DaysVariant,
            'monthlyIn' => $monthlyIn,
            'lastMonthIn' => $lastMonthIn,
            'inTrendPercent' => $inTrend['percent'],
            'inTrendBadge' => $inTrend['badge'],
            'inTrendVariant' => $inTrend['variant'],
            'monthlyOut' => $monthlyOut,
            'lastMonthOut' => $lastMonthOut,
            'outTrendPercent' => $outTrend['percent'],
            'outTrendBadge' => $outTrend['badge'],
            'outTrendVariant' => $outTrend['variant'],
        ];
    }

    /**
     * Rumus persen tren PRD §11.4: (bulan ini - bulan lalu) / bulan lalu * 100.
     * Jika pembanding bernilai 0, tampilkan "—".
     */
    public function calculateTrend(int $current, int $previous): array
    {
        if ($previous === 0) {
            return [
                'percent' => null,
                'badge' => '—',
                'variant' => 'neutral',
            ];
        }

        $percent = round((($current - $previous) / $previous) * 100, 1);
        $absFormatted = number_format(abs($percent), floor(abs($percent)) == abs($percent) ? 0 : 1, ',', '.');

        if ($percent > 0) {
            return [
                'percent' => $percent,
                'badge' => "▲ +{$absFormatted}%",
                'variant' => 'tren-naik',
            ];
        }

        if ($percent < 0) {
            return [
                'percent' => $percent,
                'badge' => "▼ −{$absFormatted}%",
                'variant' => 'tren-turun',
            ];
        }

        return [
            'percent' => 0.0,
            'badge' => '0%',
            'variant' => 'neutral',
        ];
    }

    /**
     * Data panel Level Stok: jumlah menipis, jumlah habis, dan 5 barang paling kritis (PRD §11.4 & DESIGN §6.5).
     */
    public function getStockLevelData(?int $totalProducts = null): array
    {
        $totalProducts ??= Product::count();
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        // Urutkan dari rasio stok / stok_minimum terkecil; barang habis (stok = 0) otomatis paling atas
        $criticalProducts = Product::with('unit')
            ->lowStock()
            ->orderByRaw('CASE WHEN stok = 0 THEN 0 ELSE 1 END ASC')
            ->orderByRaw('(stok * 1.0 / NULLIF(stok_minimum, 0)) ASC')
            ->orderBy('stok')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->map(function (Product $product) {
                $ratioPercent = ($product->stok <= 0 || $product->stok_minimum <= 0)
                    ? 0
                    : (int) round(min($product->stok / $product->stok_minimum, 1) * 100);

                return [
                    'product' => $product,
                    'ratio_percent' => $ratioPercent,
                ];
            });

        return [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'criticalProducts' => $criticalProducts,
        ];
    }

    /**
     * Tabel Daftar Barang di dashboard: 5 baris terbaru dengan dukungan pencarian cepat (DESIGN §6.7).
     */
    public function getDashboardProducts(?string $search = null): Collection
    {
        $query = Product::with(['category', 'unit']);

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('kode_barang', 'like', "%{$term}%")
                    ->orWhere('nama', 'like', "%{$term}%");
            });
        }

        return $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    /**
     * Data grafik Barang Masuk vs Keluar untuk 12 bulan terakhir atau 30 hari terakhir (PRD §11.4 & DESIGN §6.4).
     * Bulan atau hari tanpa transaksi diisi 0.
     */
    public function getMovementChartData(string $range = '12m', ?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : now();

        if ($range === '30d') {
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();

            $rows = StockMovement::query()
                ->join('products', 'stock_movements.product_id', '=', 'products.id')
                ->whereNull('products.deleted_at')
                ->whereBetween('stock_movements.created_at', [$startDate, $endDate])
                ->whereIn('stock_movements.tipe', ['in', 'out'])
                ->select([
                    'stock_movements.created_at',
                    'stock_movements.tipe',
                    'stock_movements.jumlah',
                ])
                ->get();

            $grouped = [];
            foreach ($rows as $row) {
                $dateKey = Carbon::parse($row->created_at)->format('Y-m-d');
                $grouped[$dateKey][$row->tipe] = ($grouped[$dateKey][$row->tipe] ?? 0) + (int) $row->jumlah;
            }

            $labels = [];
            $inSeries = [];
            $outSeries = [];

            for ($i = 0; $i < 30; $i++) {
                $day = $startDate->copy()->addDays($i);
                $key = $day->format('Y-m-d');
                $labels[] = $day->translatedFormat('d M');
                $inSeries[] = (int) ($grouped[$key]['in'] ?? 0);
                $outSeries[] = (int) ($grouped[$key]['out'] ?? 0);
            }

            return [
                'range' => '30d',
                'labels' => $labels,
                'in' => $inSeries,
                'out' => $outSeries,
                'totalIn' => array_sum($inSeries),
                'totalOut' => array_sum($outSeries),
            ];
        }

        // Default: 12 Bulan Terakhir
        $startMonth = $now->copy()->startOfMonth()->subMonths(11);
        $endMonth = $now->copy()->endOfMonth();

        $rows = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereBetween('stock_movements.created_at', [$startMonth, $endMonth])
            ->whereIn('stock_movements.tipe', ['in', 'out'])
            ->select([
                'stock_movements.created_at',
                'stock_movements.tipe',
                'stock_movements.jumlah',
            ])
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $monthKey = Carbon::parse($row->created_at)->format('Y-m');
            $grouped[$monthKey][$row->tipe] = ($grouped[$monthKey][$row->tipe] ?? 0) + (int) $row->jumlah;
        }

        $labels = [];
        $inSeries = [];
        $outSeries = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $inSeries[] = (int) ($grouped[$key]['in'] ?? 0);
            $outSeries[] = (int) ($grouped[$key]['out'] ?? 0);
        }

        return [
            'range' => '12m',
            'labels' => $labels,
            'in' => $inSeries,
            'out' => $outSeries,
            'totalIn' => array_sum($inSeries),
            'totalOut' => array_sum($outSeries),
        ];
    }

    /**
     * Stok per kategori: 4 kategori dengan stok terbesar + "Lainnya" (PRD §11.4 & DESIGN §6.3).
     */
    public function getCategoryStockData(?int $totalStock = null): array
    {
        $totalStock ??= (int) Product::sum('stok');
        $palette = ['#1E40AF', '#2563EB', '#60A5FA', '#BFDBFE', '#E2E8F0'];

        $categories = Category::query()
            ->leftJoin('products', function ($join) {
                $join->on('categories.id', '=', 'products.category_id')
                    ->whereNull('products.deleted_at');
            })
            ->select('categories.id', 'categories.nama', DB::raw('COALESCE(SUM(products.stok), 0) as total_stok'))
            ->groupBy('categories.id', 'categories.nama')
            ->havingRaw('COALESCE(SUM(products.stok), 0) > 0')
            ->orderByDesc('total_stok')
            ->orderBy('categories.nama')
            ->get();

        if ($categories->isEmpty()) {
            return [
                'totalStock' => $totalStock,
                'segments' => [],
            ];
        }

        $topFour = $categories->take(4);
        $others = $categories->slice(4);

        $segments = [];
        foreach ($topFour->values() as $idx => $cat) {
            $stok = (int) $cat->total_stok;
            $percent = $totalStock > 0 ? round(($stok / $totalStock) * 100, 1) : 0.0;
            $segments[] = [
                'label' => $cat->nama,
                'stok' => $stok,
                'percent' => $percent,
                'color' => $palette[$idx],
            ];
        }

        if ($others->isNotEmpty()) {
            $othersStock = (int) $others->sum('total_stok');
            if ($othersStock > 0) {
                $segments[] = [
                    'label' => 'Lainnya',
                    'stok' => $othersStock,
                    'percent' => $totalStock > 0 ? round(($othersStock / $totalStock) * 100, 1) : 0.0,
                    'color' => $palette[4],
                ];
            }
        }

        return [
            'totalStock' => $totalStock,
            'segments' => $segments,
        ];
    }

    /**
     * 5 baris item dari transaksi barang masuk terbaru (DESIGN §6.6).
     */
    public function getRecentStockInItems(): Collection
    {
        return StockInItem::with(['product.unit', 'stockIn'])
            ->join('stock_ins', 'stock_in_items.stock_in_id', '=', 'stock_ins.id')
            ->join('products', 'stock_in_items.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->select('stock_in_items.*')
            ->orderByDesc('stock_ins.tanggal')
            ->orderByDesc('stock_in_items.id')
            ->limit(5)
            ->get();
    }

    /**
     * 5 barang paling sering keluar dalam 30 hari terakhir beserta persentase & sparkline 4 minggu (PRD §11.4 & DESIGN §6.8a).
     */
    public function getTopOutgoingProducts(?Carbon $now = null): Collection
    {
        $now = $now ? $now->copy() : now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $totalOut30d = (int) StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('stock_movements.tipe', 'out')
            ->where('stock_movements.created_at', '>=', $thirtyDaysAgo)
            ->sum('stock_movements.jumlah');

        if ($totalOut30d === 0) {
            return collect();
        }

        $topRows = StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('stock_movements.tipe', 'out')
            ->where('stock_movements.created_at', '>=', $thirtyDaysAgo)
            ->select('stock_movements.product_id', DB::raw('SUM(stock_movements.jumlah) as total_out'))
            ->groupBy('stock_movements.product_id')
            ->orderByDesc('total_out')
            ->orderBy('stock_movements.product_id')
            ->limit(5)
            ->get();

        $productIds = $topRows->pluck('product_id')->all();
        $products = Product::with('unit')->whereIn('id', $productIds)->get()->keyBy('id');

        // Ambil seluruh pergerakan OUT 28 hari terakhir untuk 5 barang teratas dalam satu query (tanpa N+1)
        $twentyEightDaysAgo = $now->copy()->subDays(28);
        $weeklyMovements = StockMovement::query()
            ->whereIn('product_id', $productIds)
            ->where('tipe', 'out')
            ->where('created_at', '>=', $twentyEightDaysAgo)
            ->get(['product_id', 'jumlah', 'created_at']);

        // Total keluar seluruh barang pada minggu lalu (hari ke -14 s/d -7) untuk perhitungan porsi minggu lalu
        $lastWeekStart = $now->copy()->subDays(14);
        $lastWeekEnd = $now->copy()->subDays(7);
        $totalLastWeekOut = (int) StockMovement::query()
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('stock_movements.tipe', 'out')
            ->whereBetween('stock_movements.created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('stock_movements.jumlah');

        return $topRows->map(function ($row) use ($products, $weeklyMovements, $totalOut30d, $totalLastWeekOut, $now) {
            $product = $products->get($row->product_id);
            $totalOut = (int) $row->total_out;
            $percent = round(($totalOut / $totalOut30d) * 100, 1);

            // 4 titik mingguan dari paling lama (minggu ke-1) ke terbaru (minggu ke-4)
            $points = [0, 0, 0, 0];
            foreach ($weeklyMovements->where('product_id', $row->product_id) as $mov) {
                $daysAgo = Carbon::parse($mov->created_at)->diffInDays($now);
                $bucket = 3 - min(3, intdiv((int) $daysAgo, 7));
                $points[$bucket] += (int) $mov->jumlah;
            }

            $lastWeekPercent = $totalLastWeekOut > 0
                ? round(($points[2] / $totalLastWeekOut) * 100, 1)
                : 0.0;

            return [
                'product' => $product,
                'total_out' => $totalOut,
                'percent' => $percent,
                'percent_formatted' => number_format($percent, floor($percent) == $percent ? 0 : 1, ',', '.'),
                'last_week_percent_formatted' => number_format($lastWeekPercent, floor($lastWeekPercent) == $lastWeekPercent ? 0 : 1, ',', '.'),
                'sparkline' => $points,
            ];
        });
    }

    /**
     * 5 aktivitas transaksi terbaru gabungan stock_ins dan stock_outs diurutkan menurut created_at (PRD §11.4 & DESIGN §6.8b).
     */
    public function getRecentActivities(): Collection
    {
        $ins = StockIn::with('user')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (StockIn $in) => [
                'tipe' => 'in',
                'no_transaksi' => $in->no_transaksi,
                'url' => route('stock-ins.show', $in),
                'user_name' => $in->user?->name ?? 'Petugas',
                'user_initials' => $in->user?->initials ?? 'PT',
                'items_count' => (int) $in->items_count,
                'created_at' => $in->created_at,
                'group_label' => $this->formatActivityGroupLabel($in->created_at),
                'time_label' => $in->created_at?->translatedFormat('d M Y · H:i') ?? '',
            ]);

        $outs = StockOut::with('user')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (StockOut $out) => [
                'tipe' => 'out',
                'no_transaksi' => $out->no_transaksi,
                'url' => route('stock-outs.show', $out),
                'user_name' => $out->user?->name ?? 'Petugas',
                'user_initials' => $out->user?->initials ?? 'PT',
                'items_count' => (int) $out->items_count,
                'created_at' => $out->created_at,
                'group_label' => $this->formatActivityGroupLabel($out->created_at),
                'time_label' => $out->created_at?->translatedFormat('d M Y · H:i') ?? '',
            ]);

        return $ins->concat($outs)
            ->sortByDesc(fn (array $item) => $item['created_at']?->getTimestamp() ?? 0)
            ->take(5)
            ->values();
    }

    protected function formatActivityGroupLabel(?Carbon $date): string
    {
        if (! $date) {
            return 'Lainnya';
        }

        if ($date->isToday()) {
            return 'Hari ini';
        }

        if ($date->isYesterday()) {
            return 'Kemarin';
        }

        return $date->translatedFormat('d M Y');
    }
}
