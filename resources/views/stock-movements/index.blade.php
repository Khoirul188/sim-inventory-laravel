<x-app-layout title="Kartu Stok">
    <x-page-header
        title="Kartu Stok"
        :breadcrumbs="['Transaksi' => null, 'Kartu Stok' => route('stock-movements.index')]"
    />

    <!-- Bar Filter Barang & Rentang Tanggal (DESIGN §7 & PRD §7.8) -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('stock-movements.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-72">
                <label for="product_id" class="sr-only">Pilih Barang</label>
                <x-select id="product_id" name="product_id">
                    <option value="">Semua Barang</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>
                            {{ $product->kode_barang }} — {{ $product->nama }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-full sm:w-40">
                <label for="tipe" class="sr-only">Jenis Pergerakan</label>
                <x-select id="tipe" name="tipe">
                    <option value="">Semua Jenis</option>
                    <option value="in" @selected(request('tipe') === 'in')>Masuk (IN)</option>
                    <option value="out" @selected(request('tipe') === 'out')>Keluar (OUT)</option>
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
                <x-button type="submit" variant="primary">Tampilkan</x-button>
                @if (request()->hasAny(['product_id', 'tipe', 'dari', 'sampai']))
                    <x-button :href="route('stock-movements.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Ringkasan Barang Terpilih (Bila difilter per barang) -->
    @if ($selectedProduct)
        <x-card class="mb-5 !p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-subtle">{{ $selectedProduct->kode_barang }} · {{ $selectedProduct->category?->nama }}</p>
                    <h2 class="text-base font-semibold text-ink mt-0.5">
                        <a href="{{ route('products.show', $selectedProduct) }}" class="hover:text-primary-600">
                            {{ $selectedProduct->nama }}
                        </a>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-subtle">Stok Saat Ini</p>
                        <p class="text-base font-bold text-ink tabular-nums">
                            {{ format_angka($selectedProduct->stok) }} {{ $selectedProduct->unit?->singkatan }}
                        </p>
                    </div>
                    <x-badge-stok :product="$selectedProduct" />
                </div>
            </div>
        </x-card>
    @endif

    <!-- Tabel Riwayat Pergerakan Stok -->
    <x-card class="!p-0 overflow-hidden">
        @if ($movements->isEmpty())
            <x-empty-state
                icon="clipboard-document-list"
                title="Belum ada riwayat kartu stok"
                description="Riwayat pergerakan stok masuk dan keluar akan tercatat secara otomatis di halaman ini."
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Barang</th>
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
                        <td class="px-5 py-3.5 text-sm font-medium text-ink">
                            @if ($movement->product)
                                <a href="{{ route('products.show', $movement->product) }}" class="hover:text-primary-600 transition-colors duration-150">
                                    {{ $movement->product->nama }}
                                </a>
                                <p class="text-xs text-subtle tabular-nums mt-0.5">{{ $movement->product->kode_barang }}</p>
                            @else
                                —
                            @endif
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
                        <td class="px-5 py-3.5 text-sm whitespace-nowrap">
                            @if ($movement->tipe === 'in')
                                <x-badge variant="masuk">⬇ Masuk</x-badge>
                            @elseif ($movement->tipe === 'out')
                                <x-badge variant="keluar">⬆ Keluar</x-badge>
                            @else
                                <x-badge variant="info">Penyesuaian</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-semibold tabular-nums whitespace-nowrap {{ $movement->tipe === 'in' ? 'text-success-ink' : 'text-danger-ink' }}">
                            {{ $movement->tipe === 'in' ? '+' : '−' }}{{ format_angka($movement->jumlah) }}
                            <span class="font-normal text-subtle">{{ $movement->product?->unit?->singkatan }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums whitespace-nowrap">
                            {{ format_angka($movement->stok_sebelum) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-medium text-ink tabular-nums whitespace-nowrap">
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
</x-app-layout>

