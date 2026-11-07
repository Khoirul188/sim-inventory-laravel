@php
    $canCreate = auth()->user()?->hasAnyRole(['admin', 'staff']);
@endphp

<x-app-layout title="Barang Keluar">
    <x-page-header
        title="Barang Keluar"
        :breadcrumbs="['Transaksi' => null, 'Barang Keluar' => route('stock-outs.index')]"
    >
        @if ($canCreate)
            <x-button :href="route('stock-outs.create')" variant="primary" class="w-full sm:w-auto">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Barang Keluar</span>
            </x-button>
        @endif
    </x-page-header>

    <!-- Bar Filter (PRD §7.8) -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('stock-outs.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-60">
                <label for="search" class="sr-only">Cari nomor transaksi atau tujuan</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari no. transaksi / tujuan…"
                />
            </div>

            <div class="w-full sm:w-48">
                <label for="alasan" class="sr-only">Filter alasan</label>
                <x-select id="alasan" name="alasan">
                    <option value="">Semua Alasan</option>
                    @foreach ($alasanOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('alasan') === $key)>
                            {{ $label }}
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
                @if (request()->hasAny(['search', 'alasan', 'dari', 'sampai']))
                    <x-button :href="route('stock-outs.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Transaksi Barang Keluar (DESIGN §7) -->
    <x-card class="!p-0 overflow-hidden">
        @if ($stockOuts->isEmpty())
            <x-empty-state
                icon="arrow-up-tray"
                title="Belum ada transaksi barang keluar"
                description="Transaksi pengeluaran barang dari gudang akan tercatat di sini."
                :action-text="$canCreate ? '+ Tambah Barang Keluar' : null"
                :action-href="$canCreate ? route('stock-outs.create') : null"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left">No. Transaksi</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Alasan</th>
                    <th class="px-5 py-3 text-left">Tujuan / Penerima</th>
                    <th class="px-5 py-3 text-right">Jumlah Item</th>
                    <th class="px-5 py-3 text-right">Total Unit</th>
                    <th class="px-5 py-3 text-left">Oleh</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($stockOuts as $stockOut)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                            <a href="{{ route('stock-outs.show', $stockOut) }}" class="hover:text-primary-600 transition-colors duration-150">
                                {{ $stockOut->no_transaksi }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted tabular-nums whitespace-nowrap">
                            {{ $stockOut->tanggal?->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-sm whitespace-nowrap">
                            <x-badge variant="keluar">{{ $stockOut->alasan_label }}</x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-ink">
                            {{ $stockOut->tujuan ?: '—' }}
                            @if ($stockOut->keterangan)
                                <p class="text-xs text-subtle mt-0.5 truncate max-w-xs">{{ $stockOut->keterangan }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                            {{ format_angka($stockOut->items_count) }} jenis
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-danger-ink tabular-nums whitespace-nowrap">
                            −{{ format_angka($stockOut->items_sum_jumlah ?? 0) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $stockOut->user?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right whitespace-nowrap">
                            <x-button
                                :href="route('stock-outs.show', $stockOut)"
                                variant="icon"
                                aria-label="Lihat detail {{ $stockOut->no_transaksi }}"
                            >
                                <x-icon name="eye" class="w-4 h-4" />
                            </x-button>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $stockOuts->firstItem() }}–{{ $stockOuts->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $stockOuts->total() }}</span>
                    </div>
                    <div>
                        {{ $stockOuts->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

