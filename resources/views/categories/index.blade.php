<x-app-layout title="Kategori Barang">
    <x-page-header
        title="Kategori Barang"
        :breadcrumbs="['Data Master' => null, 'Kategori' => route('categories.index')]"
    >
        <x-button :href="route('categories.create')" variant="primary" class="w-full sm:w-auto">
            <x-icon name="plus" class="w-4 h-4" />
            <span>Tambah Kategori</span>
        </x-button>
    </x-page-header>

    <!-- Bar Pencarian -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('categories.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-72">
                <label for="search" class="sr-only">Cari nama atau deskripsi kategori</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari nama atau deskripsi kategori…"
                />
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">Cari</x-button>
                @if (request()->filled('search'))
                    <x-button :href="route('categories.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Kategori -->
    <x-card class="!p-0 overflow-hidden">
        @if ($categories->isEmpty())
            <x-empty-state
                icon="rectangle-group"
                title="Belum ada kategori barang"
                description="Tambahkan kategori untuk mengelompokkan data barang di gudang."
                action-text="+ Tambah Kategori"
                :action-href="route('categories.create')"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left w-14">#</th>
                    <th class="px-5 py-3 text-left">Nama Kategori</th>
                    <th class="px-5 py-3 text-left">Deskripsi</th>
                    <th class="px-5 py-3 text-right">Jumlah Barang</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($categories as $index => $category)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">
                            {{ $categories->firstItem() + $index }}
                        </td>
                        <td class="px-5 py-3.5 text-sm font-medium text-ink">
                            {{ $category->nama }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $category->deskripsi ?: '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right tabular-nums">
                            {{ format_angka($category->products_count) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <x-button
                                    :href="route('categories.edit', $category)"
                                    variant="icon"
                                    aria-label="Ubah {{ $category->nama }}"
                                >
                                    <x-icon name="pencil-square" class="w-4 h-4" />
                                </x-button>

                                <x-button
                                    type="button"
                                    variant="icon"
                                    class="hover:text-danger"
                                    aria-label="Hapus {{ $category->nama }}"
                                    x-data
                                    @click="$dispatch('open-confirm-delete-category-{{ $category->id }}')"
                                >
                                    <x-icon name="trash" class="w-4 h-4" />
                                </x-button>

                                <x-confirm-modal
                                    name="delete-category-{{ $category->id }}"
                                    title="Hapus Kategori"
                                    message="Apakah Anda yakin ingin menghapus kategori {{ $category->nama }}?"
                                    confirm-text="Hapus Kategori"
                                    :action="route('categories.destroy', $category)"
                                    method="DELETE"
                                />
                            </div>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $categories->firstItem() }}–{{ $categories->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $categories->total() }}</span>
                    </div>
                    <div>
                        {{ $categories->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

