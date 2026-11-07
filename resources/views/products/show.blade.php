@php
    $canManage = auth()->user()?->hasAnyRole(['admin', 'staff']);
@endphp

<x-app-layout :title="$product->nama">
    <x-page-header
        :title="$product->nama . ' (' . $product->kode_barang . ')'"
        :breadcrumbs="['Data Master' => null, 'Barang' => route('products.index'), $product->kode_barang => null]"
    >
        @if ($canManage)
            <x-button :href="route('products.edit', $product)" variant="primary">
                <x-icon name="pencil-square" class="w-4 h-4" />
                <span>Ubah</span>
            </x-button>
        @endif
        <x-button :href="route('products.index')" variant="secondary">
            <span>Kembali</span>
        </x-button>
    </x-page-header>

    <div class="space-y-6">
        <!-- Kartu Atas: Informasi & Foto Barang (DESIGN §7 & PRD §14.5g) -->
        <x-card>
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Foto 160 px di kiri -->
                <div class="shrink-0">
                    @if ($product->foto_url)
                        <img
                            src="{{ $product->foto_url }}"
                            alt="{{ $product->nama }}"
                            class="w-40 h-40 rounded-2xl object-cover bg-slate-100 border border-line/60"
                        />
                    @else
                        <div class="w-40 h-40 rounded-2xl bg-slate-100 text-subtle grid place-items-center border border-line/60">
                            <x-icon name="photo" class="w-12 h-12" />
                        </div>
                    @endif
                </div>

                <!-- Detail 2 Kolom -->
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8">
                    <div>
                        <p class="text-xs text-subtle">Kode Barang</p>
                        <p class="text-sm font-semibold text-ink tabular-nums mt-0.5">{{ $product->kode_barang }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Stok Saat Ini</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-lg font-bold text-ink tabular-nums">
                                {{ format_angka($product->stok) }} {{ $product->unit?->singkatan }}
                            </span>
                            <x-badge-stok :product="$product" />
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Kategori</p>
                        <p class="text-sm font-medium text-ink mt-0.5">{{ $product->category?->nama }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Stok Minimum</p>
                        <p class="text-sm font-medium text-ink tabular-nums mt-0.5">
                            {{ format_angka($product->stok_minimum) }} {{ $product->unit?->singkatan }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Satuan</p>
                        <p class="text-sm font-medium text-ink mt-0.5">
                            {{ $product->unit?->nama }} ({{ $product->unit?->singkatan }})
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Lokasi Rak</p>
                        <p class="text-sm font-medium text-ink mt-0.5">{{ $product->lokasi_rak ?: '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Harga Beli Referensi</p>
                        <p class="text-sm font-medium text-ink tabular-nums mt-0.5">{{ format_rupiah($product->harga_beli) }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-subtle">Terakhir Diperbarui</p>
                        <p class="text-sm font-medium text-ink mt-0.5">{{ $product->updated_at?->translatedFormat('d M Y') }}</p>
                    </div>

                    @if ($product->deskripsi)
                        <div class="sm:col-span-2 pt-2 border-t border-line">
                            <p class="text-xs text-subtle">Deskripsi</p>
                            <p class="text-sm text-muted mt-1">{{ $product->deskripsi }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-card>

        <!-- Kartu Bawah: Kartu Stok (DESIGN §7 & PRD §14.5g) -->
        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 md:p-6 border-b border-line flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Kartu Stok</h2>
                    <p class="text-xs text-subtle mt-0.5">Riwayat pergerakan stok untuk barang ini.</p>
                </div>

                <form method="GET" action="{{ route('products.show', $product) }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="dari" class="text-xs font-medium text-subtle">Dari:</label>
                        <x-input id="dari" name="dari" type="date" :value="request('dari')" class="w-40" />
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="sampai" class="text-xs font-medium text-subtle">Sampai:</label>
                        <x-input id="sampai" name="sampai" type="date" :value="request('sampai')" class="w-40" />
                    </div>
                    <x-button type="submit" variant="primary">Tampilkan</x-button>
                    @if (request()->hasAny(['dari', 'sampai']))
                        <x-button :href="route('products.show', $product)" variant="secondary">Reset</x-button>
                    @endif
                </form>
            </div>

            @if ($movements->isEmpty())
                <x-empty-state
                    icon="clipboard-document-list"
                    title="Belum ada riwayat kartu stok"
                    description="Riwayat pergerakan stok akan tampil setelah ada transaksi barang masuk atau barang keluar."
                />
            @else
                <x-table>
                    <x-slot name="head">
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Referensi</th>
                        <th class="px-5 py-3 text-left">Jenis</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-right">Sebelum</th>
                        <th class="px-5 py-3 text-right">Sesudah</th>
                        <th class="px-5 py-3 text-left">Oleh</th>
                    </x-slot>

                    @foreach ($movements as $movement)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3.5 text-sm text-muted tabular-nums whitespace-nowrap">
                                {{ $movement->created_at?->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-medium text-ink tabular-nums whitespace-nowrap">
                                @if ($movement->url_referensi)
                                    <a href="{{ $movement->url_referensi }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                                        {{ $movement->nomor_referensi }}
                                    </a>
                                @else
                                    {{ $movement->nomor_referensi }}
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm">
                                @if ($movement->tipe === 'in')
                                    <x-badge variant="masuk">⬇ Masuk</x-badge>
                                @elseif ($movement->tipe === 'out')
                                    <x-badge variant="keluar">⬆ Keluar</x-badge>
                                @else
                                    <x-badge variant="info">Penyesuaian</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-semibold tabular-nums {{ $movement->tipe === 'in' ? 'text-success-ink' : 'text-danger-ink' }}">
                                {{ $movement->tipe === 'in' ? '+' : '−' }}{{ format_angka($movement->jumlah) }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums">
                                {{ format_angka($movement->stok_sebelum) }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-medium text-ink tabular-nums">
                                {{ format_angka($movement->stok_sesudah) }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-muted">
                                {{ $movement->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        <div>
                            Menampilkan <span class="font-medium text-ink tabular-nums">{{ $movements->firstItem() }}–{{ $movements->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $movements->total() }}</span>
                        </div>
                        <div>
                            {{ $movements->links() }}
                        </div>
                    </x-slot>
                </x-table>
            @endif
        </x-card>
    </div>
</x-app-layout>

