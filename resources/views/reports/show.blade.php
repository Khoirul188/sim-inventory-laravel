@php
    $defaultDari = now()->startOfMonth()->toDateString();
    $defaultSampai = now()->toDateString();

    $tabs = [
        'stok' => [
            'label' => 'Stok Saat Ini',
            'href' => route('reports.show', 'stok'),
        ],
        'masuk' => [
            'label' => 'Barang Masuk',
            'href' => route('reports.show', ['jenis' => 'masuk', 'dari' => $filters['dari'] ?? $defaultDari, 'sampai' => $filters['sampai'] ?? $defaultSampai]),
        ],
        'keluar' => [
            'label' => 'Barang Keluar',
            'href' => route('reports.show', ['jenis' => 'keluar', 'dari' => $filters['dari'] ?? $defaultDari, 'sampai' => $filters['sampai'] ?? $defaultSampai]),
        ],
        'minimum' => [
            'label' => 'Stok Minimum',
            'href' => route('reports.show', 'minimum'),
        ],
    ];

    $exportParams = array_filter([
        'jenis' => $jenis,
        'dari' => $filters['dari'] ?? null,
        'sampai' => $filters['sampai'] ?? null,
        'supplier_id' => $filters['supplier_id'] ?? null,
        'alasan' => $filters['alasan'] ?? null,
        'category_id' => $filters['category_id'] ?? null,
        'status' => $filters['status'] ?? null,
        'product_id' => $filters['product_id'] ?? null,
        'tipe' => $filters['tipe'] ?? null,
    ], fn ($v) => $v !== null && $v !== '');
    $canExport = auth()->user()?->hasAnyRole(['admin', 'pimpinan']);
    $dateFilterInvalid = in_array($jenis, ['masuk', 'keluar'], true)
        && ($errors->has('dari') || $errors->has('sampai') || empty($filters['dari']) || empty($filters['sampai']));
@endphp

<x-app-layout :title="$meta['title']">
    <x-page-header
        :title="$meta['title']"
        :breadcrumbs="['Laporan' => route('reports.show', 'stok'), $meta['title'] => null]"
    >
        @if ($canExport)
            @if ($dateFilterInvalid)
                <x-button type="button" variant="secondary" disabled title="Pilih rentang tanggal yang valid terlebih dahulu">
                    <x-icon name="arrow-down-tray" class="w-4 h-4" />
                    <span>Unduh PDF</span>
                </x-button>
                <x-button type="button" variant="primary" disabled title="Pilih rentang tanggal yang valid terlebih dahulu">
                    <x-icon name="arrow-down-tray" class="w-4 h-4" />
                    <span>Unduh Excel</span>
                </x-button>
            @else
                <x-button :href="route('reports.pdf', $exportParams)" variant="secondary">
                    <x-icon name="arrow-down-tray" class="w-4 h-4" />
                    <span>Unduh PDF</span>
                </x-button>
                <x-button :href="route('reports.excel', $exportParams)" variant="primary">
                    <x-icon name="arrow-down-tray" class="w-4 h-4" />
                    <span>Unduh Excel</span>
                </x-button>
            @endif
        @endif
    </x-page-header>

    <!-- Tab Jenis Laporan (DESIGN §7: pil aktif bg-primary-600 text-white, lain bg-white border) -->
    <div class="flex flex-wrap items-center gap-2 mb-5" role="tablist" aria-label="Jenis Laporan">
        @foreach ($tabs as $tabKey => $tab)
            <a
                href="{{ $tab['href'] }}"
                role="tab"
                aria-selected="{{ $jenis === $tabKey ? 'true' : 'false' }}"
                class="inline-flex items-center h-10 px-4 rounded-lg text-sm font-medium transition-colors duration-150 {{ $jenis === $tabKey ? 'bg-primary-600 text-white shadow-sm' : 'bg-white text-ink border border-line hover:bg-slate-50' }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <!-- Bar Filter Laporan -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('reports.show', $jenis) }}" class="flex flex-wrap items-start gap-3">
            @if (in_array($jenis, ['masuk', 'keluar'], true))
                <div class="w-full sm:w-44">
                    <x-label for="dari" :required="true">Dari Tanggal</x-label>
                    <x-input
                        id="dari"
                        name="dari"
                        type="date"
                        :value="old('dari', $filters['dari'])"
                        :has-error="$errors->has('dari')"
                    />
                    <x-input-error :messages="$errors->get('dari')" />
                </div>

                <div class="w-full sm:w-44">
                    <x-label for="sampai" :required="true">Sampai Tanggal</x-label>
                    <x-input
                        id="sampai"
                        name="sampai"
                        type="date"
                        :value="old('sampai', $filters['sampai'])"
                        :has-error="$errors->has('sampai')"
                    />
                    <x-input-error :messages="$errors->get('sampai')" />
                </div>
            @endif

            @if (in_array($jenis, ['stok', 'minimum'], true))
                <div class="w-full sm:w-60">
                    <x-label for="category_id">Kategori</x-label>
                    <x-select id="category_id" name="category_id">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) ($filters['category_id'] ?? 0) === $cat->id)>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            @endif

            @if ($jenis === 'stok')
                <div class="w-full sm:w-48">
                    <x-label for="status">Status Stok</x-label>
                    <x-select id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="aman" @selected(($filters['status'] ?? '') === 'aman')>Aman</option>
                        <option value="menipis" @selected(($filters['status'] ?? '') === 'menipis')>Menipis</option>
                        <option value="habis" @selected(($filters['status'] ?? '') === 'habis')>Habis</option>
                    </x-select>
                </div>
            @endif

            @if ($jenis === 'masuk')
                <div class="w-full sm:w-64">
                    <x-label for="supplier_id">Supplier</x-label>
                    <x-select id="supplier_id" name="supplier_id">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected((int) ($filters['supplier_id'] ?? 0) === $sup->id)>
                                {{ $sup->nama }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            @endif

            @if ($jenis === 'keluar')
                <div class="w-full sm:w-52">
                    <x-label for="alasan">Alasan</x-label>
                    <x-select id="alasan" name="alasan">
                        <option value="">Semua Alasan</option>
                        @foreach ($alasanLabels as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['alasan'] ?? '') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            @endif

            <div class="flex items-center gap-2 pt-0 sm:pt-6">
                <x-button type="submit" variant="primary">Tampilkan</x-button>
                @if ($jenis === 'stok' && ($filters['category_id'] || $filters['status']))
                    <x-button :href="route('reports.show', 'stok')" variant="secondary">Reset</x-button>
                @elseif ($jenis === 'minimum' && $filters['category_id'])
                    <x-button :href="route('reports.show', 'minimum')" variant="secondary">Reset</x-button>
                @elseif ($jenis === 'masuk')
                    <x-button :href="route('reports.show', ['jenis' => 'masuk', 'dari' => $defaultDari, 'sampai' => $defaultSampai])" variant="secondary">Reset</x-button>
                @elseif ($jenis === 'keluar')
                    <x-button :href="route('reports.show', ['jenis' => 'keluar', 'dari' => $defaultDari, 'sampai' => $defaultSampai])" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Ringkasan Laporan (PRD §15) -->
    @if ($jenis === 'stok')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Jenis Barang</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_angka($summary['totalJenisBarang']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Stok</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_angka($summary['totalStok']) }}</p>
            </x-card>
            <x-card class="!p-4 sm:col-span-2 lg:col-span-1">
                <p class="text-xs font-medium text-subtle mb-2">Subtotal Stok per Kategori</p>
                @if (empty($summary['subtotalPerKategori']))
                    <p class="text-xs text-subtle">—</p>
                @else
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-6 gap-y-1.5">
                        @foreach ($summary['subtotalPerKategori'] as $sub)
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="text-muted">{{ $sub['kategori'] }} ({{ format_angka($sub['jumlah_jenis']) }} jenis)</span>
                                <span class="font-semibold text-ink tabular-nums shrink-0">{{ format_angka($sub['total_stok']) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    @elseif ($jenis === 'masuk')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Baris Item</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_angka($summary['totalBaris']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Jumlah Masuk</p>
                <p class="text-2xl font-bold text-success-ink tabular-nums mt-1">+{{ format_angka($summary['totalJumlah']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Nilai Pembelian</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_rupiah($summary['totalNilai']) }}</p>
            </x-card>
        </div>
    @elseif ($jenis === 'keluar')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Baris Item</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_angka($summary['totalBaris']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Jumlah Keluar</p>
                <p class="text-2xl font-bold text-primary-600 tabular-nums mt-1">{{ format_angka($summary['totalJumlah']) }}</p>
            </x-card>
        </div>
    @elseif ($jenis === 'minimum')
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Jumlah Barang Menipis (Termasuk Habis)</p>
                <p class="text-2xl font-bold text-warning-ink tabular-nums mt-1">{{ format_angka($summary['jumlahMenipis']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Jumlah Barang Habis (Stok = 0)</p>
                <p class="text-2xl font-bold text-danger-ink tabular-nums mt-1">{{ format_angka($summary['jumlahHabis']) }}</p>
            </x-card>
            <x-card class="!p-4">
                <p class="text-xs font-medium text-subtle">Total Kekurangan Stok</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">{{ format_angka($summary['totalKekurangan']) }}</p>
            </x-card>
        </div>
    @endif

    <!-- Tabel Pratinjau Laporan -->
    <x-card class="!p-0 overflow-hidden">
        @if ($rows->isEmpty())
            <x-empty-state
                icon="chart-bar-square"
                title="Tidak ada data untuk laporan ini"
                description="Sesuaikan filter atau periode tanggal untuk menampilkan data laporan."
            />
        @else
            @if ($jenis === 'stok')
                <x-table>
                    <x-slot name="head">
                        <th class="px-5 py-3 text-left w-14">No</th>
                        <th class="px-5 py-3 text-left">Kode</th>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Kategori</th>
                        <th class="px-5 py-3 text-left">Satuan</th>
                        <th class="px-5 py-3 text-right">Stok</th>
                        <th class="px-5 py-3 text-right">Stok Min.</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Lokasi Rak</th>
                    </x-slot>

                    @foreach ($rows as $idx => $product)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">{{ $rows->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">{{ $product->kode_barang }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink">{{ $product->nama }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted">{{ $product->category?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted whitespace-nowrap">{{ $product->unit?->singkatan ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-ink tabular-nums whitespace-nowrap">{{ format_angka($product->stok) }}</td>
                            <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">{{ format_angka($product->stok_minimum) }}</td>
                            <td class="px-5 py-3.5 text-sm whitespace-nowrap">
                                <x-badge-stok :product="$product" />
                            </td>
                            <td class="px-5 py-3.5 text-sm text-muted whitespace-nowrap">{{ $product->lokasi_rak ?: '—' }}</td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        <div>
                            Menampilkan <span class="font-medium text-ink tabular-nums">{{ $rows->firstItem() }}–{{ $rows->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $rows->total() }}</span>
                        </div>
                        <div>{{ $rows->links() }}</div>
                    </x-slot>
                </x-table>

            @elseif ($jenis === 'masuk')
                <x-table>
                    <x-slot name="head">
                        <th class="px-5 py-3 text-left w-14">No</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">No. Transaksi</th>
                        <th class="px-5 py-3 text-left">Supplier</th>
                        <th class="px-5 py-3 text-left">Kode</th>
                        <th class="px-5 py-3 text-left">Nama Barang</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-right">Harga Beli</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                    </x-slot>

                    @foreach ($rows as $idx => $item)
                        @php
                            $subtotal = (int) $item->jumlah * (float) $item->harga_beli;
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">{{ $rows->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted tabular-nums whitespace-nowrap">
                                {{ $item->stockIn?->tanggal?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                                @if ($item->stockIn)
                                    <a href="{{ route('stock-ins.show', $item->stockIn) }}" class="text-primary-600 hover:underline">
                                        {{ $item->stockIn->no_transaksi }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-muted">{{ $item->stockIn?->supplier?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">{{ $item->product?->kode_barang ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink">
                                {{ $item->product?->nama ?? '—' }}
                                @if ($item->product?->trashed())
                                    <span class="text-xs text-subtle ml-1">(Terhapus)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-success-ink tabular-nums whitespace-nowrap">
                                +{{ format_angka($item->jumlah) }} <span class="font-normal text-subtle">{{ $item->product?->unit?->singkatan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                                {{ format_rupiah($item->harga_beli) }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-ink tabular-nums whitespace-nowrap">
                                {{ format_rupiah($subtotal) }}
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        <div>
                            Menampilkan <span class="font-medium text-ink tabular-nums">{{ $rows->firstItem() }}–{{ $rows->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $rows->total() }}</span>
                        </div>
                        <div>{{ $rows->links() }}</div>
                    </x-slot>
                </x-table>

            @elseif ($jenis === 'keluar')
                <x-table>
                    <x-slot name="head">
                        <th class="px-5 py-3 text-left w-14">No</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">No. Transaksi</th>
                        <th class="px-5 py-3 text-left">Tujuan</th>
                        <th class="px-5 py-3 text-left">Alasan</th>
                        <th class="px-5 py-3 text-left">Kode</th>
                        <th class="px-5 py-3 text-left">Nama Barang</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                    </x-slot>

                    @foreach ($rows as $idx => $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">{{ $rows->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted tabular-nums whitespace-nowrap">
                                {{ $item->stockOut?->tanggal?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                                @if ($item->stockOut)
                                    <a href="{{ route('stock-outs.show', $item->stockOut) }}" class="text-primary-600 hover:underline">
                                        {{ $item->stockOut->no_transaksi }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-muted">{{ $item->stockOut?->tujuan ?: '—' }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted whitespace-nowrap">{{ $item->stockOut?->alasan_label ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">{{ $item->product?->kode_barang ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink">
                                {{ $item->product?->nama ?? '—' }}
                                @if ($item->product?->trashed())
                                    <span class="text-xs text-subtle ml-1">(Terhapus)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-ink tabular-nums whitespace-nowrap">
                                {{ format_angka($item->jumlah) }} <span class="font-normal text-subtle">{{ $item->product?->unit?->singkatan }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        <div>
                            Menampilkan <span class="font-medium text-ink tabular-nums">{{ $rows->firstItem() }}–{{ $rows->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $rows->total() }}</span>
                        </div>
                        <div>{{ $rows->links() }}</div>
                    </x-slot>
                </x-table>

            @elseif ($jenis === 'minimum')
                <x-table>
                    <x-slot name="head">
                        <th class="px-5 py-3 text-left w-14">No</th>
                        <th class="px-5 py-3 text-left">Kode</th>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Kategori</th>
                        <th class="px-5 py-3 text-right">Stok</th>
                        <th class="px-5 py-3 text-right">Stok Min.</th>
                        <th class="px-5 py-3 text-right">Kekurangan</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </x-slot>

                    @foreach ($rows as $idx => $product)
                        @php
                            $kekurangan = \App\Services\ReportService::calculateShortage((int) $product->stok, (int) $product->stok_minimum);
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">{{ $rows->firstItem() + $idx }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">{{ $product->kode_barang }}</td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink">{{ $product->nama }}</td>
                            <td class="px-5 py-3.5 text-sm text-muted">{{ $product->category?->nama ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-ink tabular-nums whitespace-nowrap">
                                {{ format_angka($product->stok) }} <span class="font-normal text-subtle">{{ $product->unit?->singkatan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                                {{ format_angka($product->stok_minimum) }} <span class="font-normal text-subtle">{{ $product->unit?->singkatan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold text-danger-ink tabular-nums whitespace-nowrap">
                                {{ format_angka($kekurangan) }} <span class="font-normal text-subtle">{{ $product->unit?->singkatan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm whitespace-nowrap">
                                <x-badge-stok :product="$product" />
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        <div>
                            Menampilkan <span class="font-medium text-ink tabular-nums">{{ $rows->firstItem() }}–{{ $rows->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $rows->total() }}</span>
                        </div>
                        <div>{{ $rows->links() }}</div>
                    </x-slot>
                </x-table>
            @endif
        @endif
    </x-card>
</x-app-layout>
