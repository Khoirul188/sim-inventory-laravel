<x-app-layout title="Tambah Pengguna">
    <x-page-header
        title="Tambah Pengguna"
        :breadcrumbs="['Pengaturan' => null, 'Pengguna' => route('users.index'), 'Tambah' => null]"
    />

    <x-card class="max-w-3xl">
        <form
            method="POST"
            action="{{ route('users.store') }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-input
                    name="name"
                    label="Nama Lengkap"
                    :value="old('name')"
                    required
                    placeholder="Contoh: Budi Santoso"
                />

                <x-input
                    name="email"
                    type="email"
                    label="Email"
                    :value="old('email')"
                    required
                    placeholder="budi@inventory.test"
                />

                <x-select name="role" label="Peran" required>
                    <option value="">Pilih peran…</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="staff" @selected(old('role', 'staff') === 'staff')>Staff Gudang</option>
                    <option value="pimpinan" @selected(old('role') === 'pimpinan')>Pimpinan</option>
                </x-select>

                <div>
                    <x-label for="is_active" value="Status Akun" :required="true" />
                    <label for="is_active" class="inline-flex items-center gap-2.5 h-10 cursor-pointer">
                        <input
                            id="is_active"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', '1') == '1')
                            class="rounded border-line text-primary-600 focus:ring-primary-600/40"
                        />
                        <span class="text-sm text-ink">Akun Aktif (dapat masuk ke sistem)</span>
                    </label>
                    <x-input-error :messages="$errors->get('is_active')" />
                </div>

                <x-input
                    name="password"
                    type="password"
                    label="Kata Sandi"
                    required
                    help="Minimal 8 karakter."
                />

                <x-input
                    name="password_confirmation"
                    type="password"
                    label="Konfirmasi Kata Sandi"
                    required
                />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('users.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Pengguna'">Simpan Pengguna</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

