@php
    $canManage = auth()->user()?->hasAnyRole(['admin', 'staff']);
@endphp

<x-app-layout title="Data Barang">
    <x-page-header
        title="Data Barang"
        :breadcrumbs="['Data Master' => null, 'Barang' => route('products.index')]"
    >
        @if ($canManage)
            <x-button :href="route('products.create')" variant="primary" class="w-full sm:w-auto">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Barang</span>
            </x-button>
        @endif
    </x-page-header>

    <!-- Bar Filter (DESIGN §5.8 & PRD §7.8) -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-64">
                <label for="search" class="sr-only">Cari kode atau nama barang</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari kode / nama…"
                />
            </div>

            <div class="w-full sm:w-52">
                <label for="category_id" class="sr-only">Filter kategori</label>
                <x-select id="category_id" name="category_id">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->nama }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="w-full sm:w-44">
                <label for="status" class="sr-only">Filter status stok</label>
                <x-select id="status" name="status">
                    <option value="">Semua Status Stok</option>
                    <option value="aman" @selected(request('status') === 'aman')>Aman</option>
                    <option value="menipis" @selected(request('status') === 'menipis')>Menipis</option>
                    <option value="habis" @selected(request('status') === 'habis')>Habis</option>
                </x-select>
            </div>

            <div class="w-full sm:w-44">
                <label for="sort" class="sr-only">Urutkan</label>
                <x-select id="sort" name="sort">
                    <option value="">Nama (A–Z)</option>
                    <option value="kode" @selected(request('sort') === 'kode')>Kode Barang</option>
                    <option value="stok_asc" @selected(request('sort') === 'stok_asc')>Stok Terendah</option>
                    <option value="stok_desc" @selected(request('sort') === 'stok_desc')>Stok Tertinggi</option>
                    <option value="terbaru" @selected(request('sort') === 'terbaru')>Terbaru</option>
                </x-select>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                @if (request()->hasAny(['search', 'category_id', 'status', 'sort']))
                    <x-button :href="route('products.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Barang -->
    <x-card class="!p-0 overflow-hidden">
        @if ($products->isEmpty())
            <x-empty-state
                icon="cube"
                title="Belum ada barang. Tambahkan barang pertama Anda."
                description="Data barang yang ditambahkan akan tercatat dengan stok awal 0."
                :action-text="$canManage ? '+ Tambah Barang' : null"
                :action-href="$canManage ? route('products.create') : null"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left w-16">Foto</th>
                    <th class="px-5 py-3 text-left">Kode</th>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Kategori</th>
                    <th class="px-5 py-3 text-right">Stok</th>
                    <th class="px-5 py-3 text-right">Min.</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($products as $product)
                    <tr class="hover:bg-slate-50/70">
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
                            @if ($product->lokasi_rak)
                                <p class="text-xs text-subtle mt-0.5">Rak: {{ $product->lokasi_rak }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $product->category?->nama }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right font-medium text-ink tabular-nums whitespace-nowrap">
                            {{ format_angka($product->stok) }} <span class="text-subtle font-normal">{{ $product->unit?->singkatan }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right text-muted tabular-nums">
                            {{ format_angka($product->stok_minimum) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm">
                            <x-badge-stok :product="$product" />
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <x-button
                                    :href="route('products.show', $product)"
                                    variant="icon"
                                    aria-label="Lihat detail {{ $product->nama }}"
                                >
                                    <x-icon name="eye" class="w-4 h-4" />
                                </x-button>

                                @if ($canManage)
                                    <x-button
                                        :href="route('products.edit', $product)"
                                        variant="icon"
                                        aria-label="Ubah {{ $product->nama }}"
                                    >
                                        <x-icon name="pencil-square" class="w-4 h-4" />
                                    </x-button>

                                    <x-button
                                        type="button"
                                        variant="icon"
                                        class="hover:text-danger"
                                        aria-label="Hapus {{ $product->nama }}"
                                        x-data
                                        @click="$dispatch('open-confirm-delete-product-{{ $product->id }}')"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </x-button>

                                    <x-confirm-modal
                                        name="delete-product-{{ $product->id }}"
                                        title="Hapus Barang"
                                        message="Apakah Anda yakin ingin menghapus barang {{ $product->nama }} ({{ $product->kode_barang }})?"
                                        confirm-text="Hapus Barang"
                                        :action="route('products.destroy', $product)"
                                        method="DELETE"
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $products->total() }}</span>
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

