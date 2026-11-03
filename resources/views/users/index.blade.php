<x-app-layout title="Pengguna">
    <x-page-header
        title="Kelola Pengguna"
        :breadcrumbs="['Pengaturan' => null, 'Pengguna' => route('users.index')]"
    >
        <x-button :href="route('users.create')" variant="primary" class="w-full sm:w-auto">
            <x-icon name="plus" class="w-4 h-4" />
            <span>Tambah Pengguna</span>
        </x-button>
    </x-page-header>

    <!-- Bar Filter -->
    <x-card class="mb-5 !p-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-full sm:w-64">
                <label for="search" class="sr-only">Cari nama atau email</label>
                <x-input
                    id="search"
                    name="search"
                    type="search"
                    :value="request('search')"
                    placeholder="Cari nama atau email…"
                />
            </div>

            <div class="w-full sm:w-44">
                <label for="role" class="sr-only">Filter peran</label>
                <x-select id="role" name="role">
                    <option value="">Semua Peran</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="staff" @selected(request('role') === 'staff')>Staff Gudang</option>
                    <option value="pimpinan" @selected(request('role') === 'pimpinan')>Pimpinan</option>
                </x-select>
            </div>

            <div class="w-full sm:w-44">
                <label for="status" class="sr-only">Filter status</label>
                <x-select id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </x-select>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                @if (request()->hasAny(['search', 'role', 'status']))
                    <x-button :href="route('users.index')" variant="secondary">Reset</x-button>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Pengguna -->
    <x-card class="!p-0 overflow-hidden">
        @if ($users->isEmpty())
            <x-empty-state
                icon="users"
                title="Belum ada pengguna ditemukan"
                description="Sesuaikan filter pencarian atau tambahkan pengguna baru."
                action-text="+ Tambah Pengguna"
                :action-href="route('users.create')"
            />
        @else
            <x-table>
                <x-slot name="head">
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left">Peran</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </x-slot>

                @foreach ($users as $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-sm font-medium text-ink">
                            {{ $item->name }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            {{ $item->email }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted">
                            <x-badge variant="primary">{{ $item->role_label }}</x-badge>
                        </td>
                        <td class="px-5 py-3.5 text-sm">
                            @if ($item->is_active)
                                <x-badge variant="aman">Aktif</x-badge>
                            @else
                                <x-badge variant="habis">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-sm text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <x-button
                                    :href="route('users.edit', $item)"
                                    variant="icon"
                                    aria-label="Ubah {{ $item->name }}"
                                >
                                    <x-icon name="pencil-square" class="w-4 h-4" />
                                </x-button>

                                @if (auth()->id() !== $item->id)
                                    <x-button
                                        type="button"
                                        variant="icon"
                                        class="hover:text-danger"
                                        aria-label="Hapus {{ $item->name }}"
                                        x-data
                                        @click="$dispatch('open-confirm-delete-user-{{ $item->id }}')"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </x-button>

                                    <x-confirm-modal
                                        name="delete-user-{{ $item->id }}"
                                        title="Hapus Pengguna"
                                        message="Apakah Anda yakin ingin menghapus pengguna {{ $item->name }}?"
                                        confirm-text="Hapus Pengguna"
                                        :action="route('users.destroy', $item)"
                                        method="DELETE"
                                    />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                <x-slot name="footer">
                    <div>
                        Menampilkan <span class="font-medium text-ink tabular-nums">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span> dari <span class="font-medium text-ink tabular-nums">{{ $users->total() }}</span>
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </x-slot>
            </x-table>
        @endif
    </x-card>
</x-app-layout>

