<x-app-layout title="Ubah Satuan">
    <x-page-header
        title="Ubah Satuan"
        :breadcrumbs="['Data Master' => null, 'Satuan' => route('units.index'), $unit->nama => null]"
    />

    <x-card class="max-w-xl">
        <form
            method="POST"
            action="{{ route('units.update', $unit) }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf
            @method('PUT')

            <x-input
                name="nama"
                label="Nama Satuan"
                :value="old('nama', $unit->nama)"
                required
            />

            <x-input
                name="singkatan"
                label="Singkatan"
                :value="old('singkatan', $unit->singkatan)"
                required
                help="Maksimal 10 karakter, harus unik."
            />

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('units.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Perubahan'">Simpan Perubahan</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

