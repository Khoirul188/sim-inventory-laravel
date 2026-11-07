<x-app-layout title="Ubah Kategori">
    <x-page-header
        title="Ubah Kategori"
        :breadcrumbs="['Data Master' => null, 'Kategori' => route('categories.index'), $category->nama => null]"
    />

    <x-card class="max-w-2xl">
        <form
            method="POST"
            action="{{ route('categories.update', $category) }}"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf
            @method('PUT')

            <x-input
                name="nama"
                label="Nama Kategori"
                :value="old('nama', $category->nama)"
                required
            />

            <div>
                <x-label for="deskripsi" value="Deskripsi" />
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="3"
                    placeholder="Keterangan singkat kategori (opsional)…"
                    class="w-full rounded-lg border {{ $errors->has('deskripsi') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white p-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                >{{ old('deskripsi', $category->deskripsi) }}</textarea>
                <x-input-error :messages="$errors->get('deskripsi')" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('categories.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Perubahan'">Simpan Perubahan</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

