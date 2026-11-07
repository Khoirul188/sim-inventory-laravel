@php
    $canCreate = auth()->user()?->hasAnyRole(['admin', 'staff']);
@endphp

<x-app-layout title="Barang Masuk">
    <x-page-header
        title="Barang Masuk"
        :breadcrumbs="['Transaksi' => null, 'Barang Masuk' => route('stock-ins.index')]"
    >
        @if ($canCreate)
            <x-button :href="route('stock-ins.create')" variant="primary" class="w-full sm:w-auto">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Barang Masuk</span>
            </x-button>
        @endif
    </x-page-header>

    <!-- Bar Filter (PRD §7.8) -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('stock-ins.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-60">
                <label for="search" class="sr-only">Cari nomor transaksi atau supplier</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari no. transaksi / supplier…"
                />
            </div>

            <div class="w-full sm:w-52">
                <label for="supplier_id" class="sr-only">Filter supplier</label>
                <x-select id="supplier_id" name="supplier_id">
                    <option value="">Semua Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->nama }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label for="dari" class="text-xs font-medium text-subtle shrink-0">Dari:</label>
                <x-input id="dari" name="dari" type="date" :value="request('dari')" class="w-full sm:w-40" />
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label for="sampai" class="text-xs font-medium text-subtle shrink-0">Sampai:</label>
                <x-input id="sampai" name="sampai" type="date" :value="request('sampai')" class="w-full sm:w-40" />
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                @if (request()->hasAny(['search', 'supplier_id', 'dari', 'sampai']))
                    <x-button :href="route('stock-ins.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Transaksi Barang Masuk (DESIGN §7) -->
    <x-card class="!p-0 overflow-hidden">
        @if ($stockIns->isEmpty())
            <x-empty-state
                icon="arrow-down-tray"
                title="Belum ada transaksi barang masuk"
                description="Transaksi penerimaan barang dari supplier akan tercatat di sini."
                :action-text="$canCreate ? '+ Tambah Barang Masuk' : null"
                :action-href="$canCreate ? route('stock-ins.create') : null"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left">No. Transaksi</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Supplier</th>
                    <th class="px-5 py-3 text-right">Jumlah Item</th>
                    <th class="px-5 py-3 text-right">Total Unit</th>
                    <th class="px-5 py-3 text-left">Oleh</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($stockIns as $stockIn)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                            <a href="{{ route('stock-ins.show', $stockIn) }}" class="hover:text-primary-600 transition-colors duration-150">
                                {{ $stockIn->no_transaksi }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted tabular-nums whitespace-nowrap">
                            {{ $stockIn->tanggal?->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-ink">
                            {{ $stockIn->supplier?->nama ?? '—' }}
                            @if ($stockIn->keterangan)
                                <p class="text-xs text-subtle mt-0.5 truncate max-w-xs">{{ $stockIn->keterangan }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                            {{ format_angka($stockIn->items_count) }} jenis
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-success-ink tabular-nums whitespace-nowrap">
                            +{{ format_angka($stockIn->items_sum_jumlah ?? 0) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $stockIn->user?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right whitespace-nowrap">
                            <x-button
                                :href="route('stock-ins.show', $stockIn)"
                                variant="icon"
                                aria-label="Lihat detail {{ $stockIn->no_transaksi }}"
                            >
                                <x-icon name="eye" class="w-4 h-4" />
                            </x-button>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $stockIns->firstItem() }}–{{ $stockIns->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $stockIns->total() }}</span>
                    </div>
                    <div>
                        {{ $stockIns->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

