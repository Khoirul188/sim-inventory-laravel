<x-app-layout title="Tambah Satuan">
    <x-page-header
        title="Tambah Satuan"
        :breadcrumbs="['Data Master' => null, 'Satuan' => route('units.index'), 'Tambah' => null]"
    />

    <x-card class="max-w-xl">
        <form
            method="POST"
            action="{{ route('units.store') }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf

            <x-input
                name="nama"
                label="Nama Satuan"
                :value="old('nama')"
                required
                autofocus
                placeholder="Contoh: Pieces"
            />

            <x-input
                name="singkatan"
                label="Singkatan"
                :value="old('singkatan')"
                required
                placeholder="Contoh: pcs"
                help="Maksimal 10 karakter, harus unik."
            />

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('units.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Satuan'">Simpan Satuan</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

