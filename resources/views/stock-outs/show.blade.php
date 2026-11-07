@php
    $canCreate = auth()->user()?->hasAnyRole(['admin', 'staff']);
    $totalUnit = $stockOut->items->sum('jumlah');
@endphp

<x-app-layout :title="'Barang Keluar ' . $stockOut->no_transaksi">
    <x-page-header
        :title="'Detail Barang Keluar (' . $stockOut->no_transaksi . ')'"
        :breadcrumbs="['Transaksi' => null, 'Barang Keluar' => route('stock-outs.index'), $stockOut->no_transaksi => null]"
    >
        @if ($canCreate)
            <x-button :href="route('stock-outs.create')" variant="primary">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Catat Barang Keluar</span>
            </x-button>
        @endif
        <x-button :href="route('stock-outs.index')" variant="secondary">
            <span>Kembali</span>
        </x-button>
    </x-page-header>

    <div class="space-y-6 max-w-5xl">
        <!-- Kartu Informasi Header -->
        <x-card>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <p class="text-xs text-subtle">No. Transaksi</p>
                    <p class="text-sm font-semibold text-ink tabular-nums mt-0.5">{{ $stockOut->no_transaksi }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Tanggal Pengeluaran</p>
                    <p class="text-sm font-medium text-ink tabular-nums mt-0.5">{{ $stockOut->tanggal?->translatedFormat('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Alasan</p>
                    <div class="mt-1">
                        <x-badge variant="keluar">{{ $stockOut->alasan_label }}</x-badge>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-subtle">Tujuan / Penerima</p>
                    <p class="text-sm font-medium text-ink mt-0.5">{{ $stockOut->tujuan ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Dicatat Oleh</p>
                    <p class="text-sm font-medium text-ink mt-0.5">{{ $stockOut->user?->name ?? '—' }}</p>
                </div>

                @if ($stockOut->keterangan)
                    <div class="sm:col-span-3 pt-3 sm:pt-0">
                        <p class="text-xs text-subtle">Keterangan</p>
                        <p class="text-sm text-muted mt-0.5">{{ $stockOut->keterangan }}</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Tabel Rincian Barang -->
        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 md:p-6 border-b border-line flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">Rincian Item Barang Keluar</h2>
                <x-badge variant="keluar">{{ format_angka($stockOut->items->count()) }} Jenis Barang</x-badge>
            </div>

            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left w-14">No</th>
                    <th class="px-5 py-3 text-left">Kode</th>
                    <th class="px-5 py-3 text-left">Nama Barang</th>
                    <th class="px-5 py-3 text-left">Kategori</th>
                    <th class="px-5 py-3 text-right">Jumlah Keluar</th>
                </x-slot>

                @foreach ($stockOut->items as $index => $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm text-muted tabular-nums">{{ $index + 1 }}</td>
                        <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                            {{ $item->product?->kode_barang }}
                        </td>
                        <td class="px-5 py-3.5 text-sm font-medium text-ink">
                            @if ($item->product)
                                <a href="{{ route('products.show', $item->product) }}" class="hover:text-primary-600 transition-colors duration-150">
                                    {{ $item->product->nama }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $item->product?->category?->nama ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-danger-ink tabular-nums whitespace-nowrap">
                            −{{ format_angka($item->jumlah) }} <span class="font-normal text-subtle">{{ $item->product?->unit?->singkatan }}</span>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div class="text-sm font-medium text-ink">
                        Total Jenis: <span class="font-bold text-ink tabular-nums">{{ format_angka($stockOut->items->count()) }}</span>
                    </div>
                    <div class="text-sm font-medium text-ink">
                        Total Unit Keluar: <span class="font-bold text-danger-ink tabular-nums">−{{ format_angka($totalUnit) }}</span>
                    </div>
                </x-slot>
            </x-table>
        </x-card>
    </div>
</x-app-layout>

