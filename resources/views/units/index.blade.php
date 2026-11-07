<x-app-layout title="Satuan Barang">
    <x-page-header
        title="Satuan Barang"
        :breadcrumbs="['Data Master' => null, 'Satuan' => route('units.index')]"
    >
        <x-button :href="route('units.create')" variant="primary" class="w-full sm:w-auto">
            <x-icon name="plus" class="w-4 h-4" />
            <span>Tambah Satuan</span>
        </x-button>
    </x-page-header>

    <!-- Bar Pencarian -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('units.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-72">
                <label for="search" class="sr-only">Cari nama atau singkatan satuan</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari nama atau singkatan satuan…"
                />
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">Cari</x-button>
                @if (request()->filled('search'))
                    <x-button :href="route('units.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Satuan -->
    <x-card class="!p-0 overflow-hidden">
        @if ($units->isEmpty())
            <x-empty-state
                icon="scale"
                title="Belum ada satuan barang"
                description="Tambahkan satuan barang seperti pcs, box, rim, atau kg."
                action-text="+ Tambah Satuan"
                :action-href="route('units.create')"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left w-14">#</th>
                    <th class="px-5 py-3 text-left">Nama Satuan</th>
                    <th class="px-5 py-3 text-left">Singkatan</th>
                    <th class="px-5 py-3 text-right">Jumlah Barang</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($units as $index => $unit)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm text-subtle tabular-nums">
                            {{ $units->firstItem() + $index }}
                        </td>
                        <td class="px-5 py-3.5 text-sm font-medium text-ink">
                            {{ $unit->nama }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            <x-badge variant="neutral">{{ $unit->singkatan }}</x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right tabular-nums">
                            {{ format_angka($unit->products_count) }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <x-button
                                    :href="route('units.edit', $unit)"
                                    variant="icon"
                                    aria-label="Ubah {{ $unit->nama }}"
                                >
                                    <x-icon name="pencil-square" class="w-4 h-4" />
                                </x-button>

                                <x-button
                                    type="button"
                                    variant="icon"
                                    class="hover:text-danger"
                                    aria-label="Hapus {{ $unit->nama }}"
                                    x-data
                                    @click="$dispatch('open-confirm-delete-unit-{{ $unit->id }}')"
                                >
                                    <x-icon name="trash" class="w-4 h-4" />
                                </x-button>

                                <x-confirm-modal
                                    name="delete-unit-{{ $unit->id }}"
                                    title="Hapus Satuan"
                                    message="Apakah Anda yakin ingin menghapus satuan {{ $unit->nama }} ({{ $unit->singkatan }})?"
                                    confirm-text="Hapus Satuan"
                                    :action="route('units.destroy', $unit)"
                                    method="DELETE"
                                />
                            </div>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $units->firstItem() }}–{{ $units->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $units->total() }}</span>
                    </div>
                    <div>
                        {{ $units->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

