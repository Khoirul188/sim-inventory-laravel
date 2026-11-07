<x-app-layout title="Ubah Supplier">
    <x-page-header
        title="Ubah Supplier"
        :breadcrumbs="['Data Master' => null, 'Supplier' => route('suppliers.index'), $supplier->nama => null]"
    />

    <x-card class="max-w-3xl">
        <form
            method="POST"
            action="{{ route('suppliers.update', $supplier) }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <x-input
                        name="nama"
                        label="Nama Supplier"
                        :value="old('nama', $supplier->nama)"
                        required
                    />
                </div>

                <x-input
                    name="telepon"
                    label="Nomor Telepon"
                    :value="old('telepon', $supplier->telepon)"
                    placeholder="Contoh: (0291) 431234 / 0812-3456-7890"
                />

                <x-input
                    name="email"
                    type="email"
                    label="Alamat Email"
                    :value="old('email', $supplier->email)"
                    placeholder="Contoh: sales@sumberjaya.co.id"
                />

                <div class="md:col-span-2">
                    <x-label for="alamat" value="Alamat Lengkap" />
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Alamat lengkap supplier (opsional)…"
                        class="w-full rounded-lg border {{ $errors->has('alamat') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white p-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                    >{{ old('alamat', $supplier->alamat) }}</textarea>
                    <x-input-error :messages="$errors->get('alamat')" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('suppliers.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Perubahan'">Simpan Perubahan</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

