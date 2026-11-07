@php
    $canCreate = auth()->user()?->hasAnyRole(['admin', 'staff']);
    $totalUnit = $stockIn->items->sum('jumlah');
    $totalNilai = $stockIn->items->sum(fn ($item) => $item->jumlah * (float) $item->harga_beli);
@endphp

<x-app-layout :title="'Barang Masuk ' . $stockIn->no_transaksi">
    <x-page-header
        :title="'Detail Barang Masuk (' . $stockIn->no_transaksi . ')'"
        :breadcrumbs="['Transaksi' => null, 'Barang Masuk' => route('stock-ins.index'), $stockIn->no_transaksi => null]"
    >
        @if ($canCreate)
            <x-button :href="route('stock-ins.create')" variant="primary">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Catat Barang Masuk</span>
            </x-button>
        @endif
        <x-button :href="route('stock-ins.index')" variant="secondary">
            <span>Kembali</span>
        </x-button>
    </x-page-header>

    <div class="space-y-6 max-w-5xl">
        <!-- Kartu Informasi Header -->
        <x-card>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <p class="text-xs text-subtle">No. Transaksi</p>
                    <p class="text-sm font-semibold text-ink tabular-nums mt-0.5">{{ $stockIn->no_transaksi }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Tanggal Penerimaan</p>
                    <p class="text-sm font-medium text-ink tabular-nums mt-0.5">{{ $stockIn->tanggal?->translatedFormat('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Supplier</p>
                    <p class="text-sm font-medium text-ink mt-0.5">{{ $stockIn->supplier?->nama ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs text-subtle">Dicatat Oleh</p>
                    <p class="text-sm font-medium text-ink mt-0.5">{{ $stockIn->user?->name ?? '—' }}</p>
                </div>

                @if ($stockIn->keterangan)
                    <div class="sm:col-span-2 md:col-span-4 pt-3 border-t border-line">
                        <p class="text-xs text-subtle">Keterangan</p>
                        <p class="text-sm text-muted mt-1">{{ $stockIn->keterangan }}</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Tabel Rincian Barang -->
        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 md:p-6 border-b border-line flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">Rincian Item Barang Masuk</h2>
                <x-badge variant="masuk">{{ format_angka($stockIn->items->count()) }} Jenis Barang</x-badge>
            </div>

            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left w-14">No</th>
                    <th class="px-5 py-3 text-left">Kode</th>
                    <th class="px-5 py-3 text-left">Nama Barang</th>
                    <th class="px-5 py-3 text-right">Jumlah</th>
                    <th class="px-5 py-3 text-right">Harga Beli</th>
                    <th class="px-5 py-3 text-right">Subtotal</th>
                </x-slot>

                @foreach ($stockIn->items as $index => $item)
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
                        <td class="px-5 py-3.5 text-sm text-right font-semibold text-success-ink tabular-nums whitespace-nowrap">
                            +{{ format_angka($item->jumlah) }} <span class="font-normal text-subtle">{{ $item->product?->unit?->singkatan }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                            {{ format_rupiah($item->harga_beli) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-medium text-ink tabular-nums whitespace-nowrap">
                            {{ format_rupiah($item->jumlah * (float) $item->harga_beli) }}
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div class="text-sm font-medium text-ink">
                        Total Unit Masuk: <span class="font-bold text-success-ink tabular-nums">+{{ format_angka($totalUnit) }}</span>
                    </div>
                    <div class="text-sm font-medium text-ink">
                        Total Nilai: <span class="font-bold text-ink tabular-nums">{{ format_rupiah($totalNilai) }}</span>
                    </div>
                </x-slot>
            </x-table>
        </x-card>
    </div>
</x-app-layout>

