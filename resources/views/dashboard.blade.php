@php
    $canManage = auth()->user()?->hasAnyRole(['admin', 'staff']);
@endphp

<x-app-layout title="Dashboard" :low-stock-count="$lowStockCount">
    <div class="grid grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_320px] gap-5">
        <!-- Kolom Utama -->
        <div class="space-y-5 min-w-0">
            <!-- 1. Baris KPI (DESIGN §6.2 & PRD FR-30) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <x-stat-card
                    label="Total Jenis Barang"
                    :value="format_angka($kpis['totalProducts'])"
                    :badge-text="$kpis['newProductsBadge']"
                    :badge-variant="$kpis['newProductsVariant']"
                    subtext="minggu ini"
                    icon="cube"
                />
                <x-stat-card
                    label="Total Stok"
                    :value="format_angka($kpis['totalStock'])"
                    :badge-text="$kpis['net7DaysBadge']"
                    :badge-variant="$kpis['net7DaysVariant']"
                    subtext="7 hari terakhir"
                    icon="squares-2x2"
                />
                <x-stat-card
                    label="Barang Masuk"
                    :value="format_angka($kpis['monthlyIn'])"
                    :badge-text="$kpis['inTrendBadge']"
                    :badge-variant="$kpis['inTrendVariant']"
                    subtext="dari bulan lalu"
                    icon="arrow-down-tray"
                />
                <x-stat-card
                    label="Barang Keluar"
                    :value="format_angka($kpis['monthlyOut'])"
                    :badge-text="$kpis['outTrendBadge']"
                    :badge-variant="$kpis['outTrendVariant']"
                    subtext="dari bulan lalu"
                    icon="arrow-up-tray"
                />
            </div>

            <!-- 2. Level Stok — Progress Bar (DESIGN §6.5 & PRD FR-31) -->
            <x-card title="Level Stok">
                <x-slot name="action">
                    @if ($lowStockCount > 0)
                        <x-badge variant="warning">Perlu Perhatian</x-badge>
                    @else
                        <x-badge variant="success">Aman</x-badge>
                    @endif
                </x-slot>

                <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4 pb-4 border-b border-line">
                    <div>
                        <p class="text-xs font-medium text-subtle">Barang Menipis</p>
                        <p class="mt-1">
                            <span class="text-2xl font-bold text-ink tabular-nums">{{ format_angka($lowStockCount) }}</span>
                            <span class="text-sm text-subtle tabular-nums">/ {{ format_angka($kpis['totalProducts']) }} jenis barang</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-subtle">Stok Habis:</span>
                        <span class="text-sm font-semibold {{ $outOfStockCount > 0 ? 'text-danger-ink' : 'text-ink' }} tabular-nums ml-1">
                            {{ format_angka($outOfStockCount) }}
                        </span>
                    </div>
                </div>

                @if ($criticalProducts->isEmpty())
                    <div class="py-6 flex flex-col items-center justify-center text-center">
                        <div class="w-11 h-11 rounded-full bg-success-soft text-success-ink grid place-items-center mb-2.5">
                            <x-icon name="check-circle" class="w-6 h-6" />
                        </div>
                        <p class="text-sm font-medium text-ink">Semua stok di atas batas minimum.</p>
                        <p class="text-xs text-subtle mt-0.5">Tidak ada barang yang memerlukan restock mendesak saat ini.</p>
                    </div>
                @else
                    <div class="space-y-3.5">
                        @foreach ($criticalProducts as $item)
                            @php
                                $product = $item['product'];
                            @endphp
                            <div>
                                <div class="flex items-center justify-between gap-3 mb-1.5">
                                    <a
                                        href="{{ route('products.show', $product) }}"
                                        class="text-sm font-medium text-ink hover:text-primary-600 truncate transition-colors duration-150"
                                    >
                                        {{ $product->nama }}
                                    </a>
                                    <div class="text-sm text-muted tabular-nums whitespace-nowrap shrink-0">
                                        @if ($product->stok === 0)
                                            <span class="font-semibold text-danger-ink mr-1">Habis</span>
                                            <span class="text-xs text-subtle">(0 {{ $product->unit?->singkatan }} dari min. {{ format_angka($product->stok_minimum) }})</span>
                                        @else
                                            <span class="font-semibold text-ink">{{ format_angka($product->stok) }} {{ $product->unit?->singkatan }}</span>
                                            <span class="text-subtle">dari min. {{ format_angka($product->stok_minimum) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-warning transition-all duration-300"
                                        style="width: {{ $item['ratio_percent'] }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-3 border-t border-line flex items-center justify-between">
                        <a
                            href="{{ route('products.index', ['status' => 'menipis']) }}"
                            class="text-xs font-semibold text-primary-600 hover:text-primary-800 transition-colors duration-150"
                        >
                            Lihat semua ({{ format_angka($lowStockCount) }}) →
                        </a>
                        @if ($canManage)
                            <a
                                href="{{ route('stock-ins.create') }}"
                                class="text-xs font-medium text-muted hover:text-ink transition-colors duration-150"
                            >
                                + Catat Barang Masuk
                            </a>
                        @endif
                    </div>
                @endif
            </x-card>

            <!-- 3. Daftar Barang — 5 Baris Terbaru (DESIGN §6.7) -->
            <x-card class="!p-0 overflow-hidden">
                <div class="p-5 md:p-6 border-b border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Daftar Barang</h2>
                        <p class="text-xs text-subtle mt-0.5">5 data barang terbaru di gudang.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                            <input type="hidden" name="range" value="{{ $range }}" />
                            <label for="dashboard-product-search" class="sr-only">Cari barang di dashboard</label>
                            <input
                                id="dashboard-product-search"
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari kode / nama…"
                                class="h-9 w-44 sm:w-52 rounded-lg border border-line bg-white px-3 text-xs text-ink placeholder:text-subtle focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                            />
                            @if ($search)
                                <a
                                    href="{{ route('dashboard', ['range' => $range]) }}"
                                    class="text-xs text-subtle hover:text-ink px-1.5"
                                >Reset</a>
                            @endif
                        </form>

                        @if ($canManage)
                            <x-button :href="route('products.create')" variant="primary" class="!h-9 !px-3 !text-xs">
                                <x-icon name="plus" class="w-4 h-4" />
                                <span>Tambah Barang</span>
                            </x-button>
                        @endif

                        <a
                            href="{{ route('products.index') }}"
                            class="text-xs font-semibold text-primary-600 hover:text-primary-800 whitespace-nowrap ml-1"
                        >
                            Lihat semua →
                        </a>
                    </div>
                </div>

                @if ($dashboardProducts->isEmpty())
                    <x-empty-state
                        icon="cube"
                        title="Belum ada barang. Tambahkan barang pertama Anda."
                        description="Data barang yang tercatat di sistem akan tampil di tabel ringkasan ini."
                        :action-text="$canManage ? '+ Tambah Barang' : null"
                        :action-href="$canManage ? route('products.create') : null"
                    />
                @else
                    <x-table>
                        <x-slot name="head">
                            <th class="px-5 py-3 text-left w-12">#</th>
                            <th class="px-5 py-3 text-left w-16">Foto</th>
                            <th class="px-5 py-3 text-left">Kode</th>
                            <th class="px-5 py-3 text-left">Nama</th>
                            <th class="px-5 py-3 text-left">Kategori</th>
                            <th class="px-5 py-3 text-right">Stok</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </x-slot>

                        @foreach ($dashboardProducts as $idx => $product)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($product->foto_url)
                                        <img
                                            src="{{ $product->foto_url }}"
                                            alt="{{ $product->nama }}"
                                            class="h-10 w-10 rounded-lg object-cover bg-slate-100 border border-line/60"
                                        />
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-slate-100 text-subtle grid place-items-center border border-line/60">
                                            <x-icon name="photo" class="w-5 h-5" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                                    {{ $product->kode_barang }}
                                </td>
                                <td class="px-5 py-3.5 text-sm font-medium text-ink">
                                    <a href="{{ route('products.show', $product) }}" class="hover:text-primary-600 transition-colors duration-150">
                                        {{ $product->nama }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-muted">
                                    {{ $product->category?->nama }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right font-medium text-ink tabular-nums whitespace-nowrap">
                                    {{ format_angka($product->stok) }} <span class="text-subtle font-normal">{{ $product->unit?->singkatan }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-sm whitespace-nowrap">
                                    <x-badge-stok :product="$product" />
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right whitespace-nowrap">
                                    <x-button
                                        :href="route('products.show', $product)"
                                        variant="icon"
                                        aria-label="Lihat detail {{ $product->nama }}"
                                    >
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @endif
            </x-card>

            <!-- 4. Grafik Barang Masuk vs Keluar — Area Chart (DESIGN §6.4 & PRD FR-32) -->
            <x-card>
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Barang Masuk vs Keluar</h2>
                        <div class="mt-2 flex flex-wrap items-baseline gap-6">
                            <div>
                                <span class="text-2xl font-bold text-ink tabular-nums">{{ format_angka($movementChart['totalIn']) }}</span>
                                <span class="text-xs text-subtle ml-1">Total masuk</span>
                            </div>
                            <div>
                                <span class="text-2xl font-bold text-ink tabular-nums">{{ format_angka($movementChart['totalOut']) }}</span>
                                <span class="text-xs text-subtle ml-1">Total keluar</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Legenda manual -->
                        <div class="flex items-center gap-4 text-xs font-medium text-muted">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                                <span>Masuk</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary-600"></span>
                                <span>Keluar</span>
                            </span>
                        </div>

                        <!-- Toggle 12 Bulan / 30 Hari -->
                        <div class="inline-flex items-center rounded-lg border border-line bg-slate-50 p-0.5 text-xs font-medium">
                            <a
                                href="{{ route('dashboard', array_filter(['range' => '12m', 'search' => $search])) }}"
                                class="px-2.5 py-1.5 rounded-md transition-colors duration-150 {{ $range === '12m' ? 'bg-white text-ink shadow-sm font-semibold' : 'text-subtle hover:text-ink' }}"
                            >
                                12 Bulan Terakhir
                            </a>
                            <a
                                href="{{ route('dashboard', array_filter(['range' => '30d', 'search' => $search])) }}"
                                class="px-2.5 py-1.5 rounded-md transition-colors duration-150 {{ $range === '30d' ? 'bg-white text-ink shadow-sm font-semibold' : 'text-subtle hover:text-ink' }}"
                            >
                                30 Hari Terakhir
                            </a>
                        </div>
                    </div>
                </div>

                <div class="h-64 w-full relative">
                    <canvas
                        id="movement-area-chart"
                        aria-label="Grafik pergerakan barang masuk dan keluar"
                        role="img"
                        data-labels='@json($movementChart["labels"])'
                        data-in='@json($movementChart["in"])'
                        data-out='@json($movementChart["out"])'
                    ></canvas>
                </div>
            </x-card>

            <!-- Baris Bawah: 5. Stok per Kategori & 6. Barang Masuk Terakhir -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- 5. Stok per Kategori — Donut (DESIGN §6.3) -->
                <x-card>
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-ink">Stok per Kategori</h2>
                            <p class="text-xs text-subtle mt-0.5">Komposisi stok aktif menurut kategori.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-subtle">Total Stok</p>
                            <p class="text-2xl font-semibold text-ink tabular-nums">{{ format_angka($categoryStock['totalStock']) }}</p>
                        </div>
                    </div>

                    @if (empty($categoryStock['segments']))
                        <x-empty-state
                            icon="rectangle-group"
                            title="Belum ada stok per kategori"
                            description="Komposisi kategori akan tampil setelah barang memiliki stok."
                        />
                    @else
                        @php
                            $donutLabels = array_column($categoryStock['segments'], 'label');
                            $donutValues = array_column($categoryStock['segments'], 'stok');
                            $donutColors = array_column($categoryStock['segments'], 'color');
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-[200px_minmax(0,1fr)] items-center gap-6 pt-2">
                            <div class="relative h-[200px] w-[200px] mx-auto">
                                <canvas
                                    id="category-donut-chart"
                                    aria-label="Grafik komposisi stok per kategori"
                                    role="img"
                                    data-labels='@json($donutLabels)'
                                    data-values='@json($donutValues)'
                                    data-colors='@json($donutColors)'
                                ></canvas>
                                <div class="inset-0 absolute flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-xs text-subtle">Total</span>
                                    <span class="text-lg font-bold text-ink tabular-nums">{{ format_angka($categoryStock['totalStock']) }}</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach ($categoryStock['segments'] as $segment)
                                    <div class="flex items-start justify-between gap-3 text-sm">
                                        <div class="flex items-start gap-2.5 min-w-0">
                                            <span
                                                class="w-2.5 h-2.5 rounded-sm shrink-0 mt-1.5"
                                                style="background-color: {{ $segment['color'] }}"
                                            ></span>
                                            <span class="text-ink font-medium leading-snug" title="{{ $segment['label'] }}">
                                                {{ $segment['label'] }}
                                                <span class="text-xs font-normal text-subtle tabular-nums ml-1">
                                                    ({{ number_format($segment['percent'], floor($segment['percent']) == $segment['percent'] ? 0 : 1, ',', '.') }}%)
                                                </span>
                                            </span>
                                        </div>
                                        <span class="font-semibold text-ink tabular-nums shrink-0">
                                            {{ format_angka($segment['stok']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-card>

                <!-- 6. Barang Masuk Terakhir (DESIGN §6.6) -->
                <x-card title="Barang Masuk Terakhir">
                    <x-slot name="action">
                        <a
                            href="{{ route('stock-ins.index') }}"
                            class="text-xs font-semibold text-primary-600 hover:text-primary-800 transition-colors duration-150"
                        >
                            Lihat semua →
                        </a>
                    </x-slot>

                    @if ($recentStockInItems->isEmpty())
                        <x-empty-state
                            icon="arrow-down-tray"
                            title="Belum ada barang masuk"
                            description="Item penerimaan barang terbaru akan muncul di panel ini."
                            :action-text="$canManage ? '+ Tambah Barang Masuk' : null"
                            :action-href="$canManage ? route('stock-ins.create') : null"
                        />
                    @else
                        <div class="divide-y divide-line">
                            @foreach ($recentStockInItems as $item)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if ($item->product?->foto_url)
                                            <img
                                                src="{{ $item->product->foto_url }}"
                                                alt="{{ $item->product->nama }}"
                                                class="h-10 w-10 rounded-lg object-cover bg-slate-100 border border-line/60 shrink-0"
                                            />
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-slate-100 text-subtle grid place-items-center border border-line/60 shrink-0">
                                                <x-icon name="photo" class="w-5 h-5" />
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            @if ($item->product)
                                                <a
                                                    href="{{ route('products.show', $item->product) }}"
                                                    class="text-sm font-medium text-ink hover:text-primary-600 truncate block transition-colors duration-150"
                                                >
                                                    {{ $item->product->nama }}
                                                </a>
                                            @else
                                                <p class="text-sm font-medium text-ink truncate">—</p>
                                            @endif
                                            <p class="text-xs text-subtle tabular-nums mt-0.5">
                                                {{ $item->stockIn?->no_transaksi }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-semibold text-success-ink tabular-nums">
                                            +{{ format_angka($item->jumlah) }} {{ $item->product?->unit?->singkatan }}
                                        </p>
                                        <p class="text-xs text-subtle tabular-nums mt-0.5">
                                            {{ $item->stockIn?->tanggal?->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>
        </div>

        <!-- 7. Kolom Kanan (Lebar 320 px pada >= 2xl, turun ke bawah pada < 2xl — DESIGN §6.1 & §6.8) -->
        <div class="space-y-5">
            <!-- 7a. Barang Paling Sering Keluar (30 Hari Terakhir + Sparkline 4 Minggu — DESIGN §6.8a) -->
            <x-card title="Barang Paling Sering Keluar">
                @if ($topOutgoingProducts->isEmpty())
                    <x-empty-state
                        icon="arrow-up-tray"
                        title="Belum ada pengeluaran 30 hari terakhir"
                        description="5 barang dengan frekuensi keluar tertinggi akan tampil di sini."
                    />
                @else
                    <div class="divide-y divide-line">
                        @foreach ($topOutgoingProducts as $top)
                            <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    @if ($top['product'])
                                        <a
                                            href="{{ route('products.show', $top['product']) }}"
                                            class="text-sm text-muted hover:text-primary-600 truncate block transition-colors duration-150"
                                        >
                                            {{ $top['product']->nama }}
                                        </a>
                                    @endif
                                    <p class="text-2xl font-bold text-ink tabular-nums mt-0.5">
                                        {{ $top['percent_formatted'] }}%
                                    </p>
                                    @php
                                        $prevWeekOut = (int) ($top['sparkline'][2] ?? 0);
                                        $currWeekOut = (int) ($top['sparkline'][3] ?? 0);
                                        $weeklyDiffPercent = $prevWeekOut > 0 ? round((($currWeekOut - $prevWeekOut) / $prevWeekOut) * 100, 1) : null;
                                    @endphp
                                    <p class="text-xs text-subtle tabular-nums mt-0.5">
                                        {{ format_angka($top['total_out']) }} {{ $top['product']?->unit?->singkatan }} / 30 hari
                                        @if ($weeklyDiffPercent !== null)
                                            · {{ $weeklyDiffPercent > 0 ? '+' : ($weeklyDiffPercent < 0 ? '−' : '') }}{{ number_format(abs($weeklyDiffPercent), floor(abs($weeklyDiffPercent)) == abs($weeklyDiffPercent) ? 0 : 1, ',', '.') }}% dari minggu lalu
                                        @endif
                                    </p>
                                </div>

                                <div class="shrink-0">
                                    <canvas
                                        width="96"
                                        height="40"
                                        data-sparkline
                                        data-points='@json($top["sparkline"])'
                                        aria-label="Sparkline pengeluaran {{ $top['product']?->nama }}"
                                        role="img"
                                    ></canvas>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <!-- 7b. Aktivitas Terbaru (5 Transaksi Terakhir Gabungan Masuk & Keluar — DESIGN §6.8b & PRD FR-33) -->
            <x-card title="Aktivitas Terbaru">
                @if ($recentActivities->isEmpty())
                    <x-empty-state
                        icon="clipboard-document-list"
                        title="Belum ada aktivitas"
                        description="Riwayat transaksi barang masuk dan keluar terbaru akan tampil di sini."
                    />
                @else
                    @php
                        $groupedActivities = $recentActivities->groupBy('group_label');
                    @endphp
                    <div class="space-y-4">
                        @foreach ($groupedActivities as $groupLabel => $items)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-subtle mb-2.5">
                                    {{ $groupLabel }}
                                </p>
                                <div class="border-l border-line ml-2 pl-4 space-y-3.5">
                                    @foreach ($items as $act)
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-800 text-xs font-semibold grid place-items-center shrink-0">
                                                {{ $act['user_initials'] }}
                                            </div>
                                            <div class="min-w-0 text-sm leading-snug">
                                                <p class="text-muted">
                                                    <span class="font-semibold text-ink">{{ $act['user_name'] }}</span>
                                                    mencatat barang {{ $act['tipe'] === 'in' ? 'masuk' : 'keluar' }}
                                                    <a href="{{ $act['url'] }}" class="font-semibold text-primary-600 hover:underline tabular-nums">
                                                        {{ $act['no_transaksi'] }}
                                                    </a>
                                                    <span class="text-subtle tabular-nums">({{ format_angka($act['items_count']) }} item)</span>
                                                </p>
                                                <p class="text-xs text-subtle tabular-nums mt-0.5">
                                                    {{ $act['time_label'] }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
