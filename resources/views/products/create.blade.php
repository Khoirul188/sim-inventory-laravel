<x-app-layout title="Tambah Barang">
    <x-page-header
        title="Tambah Barang"
        :breadcrumbs="['Data Master' => null, 'Barang' => route('products.index'), 'Tambah' => null]"
    />

    <x-card class="max-w-4xl">
        <form
            method="POST"
            action="{{ route('products.store') }}"
            enctype="multipart/form-data"
            x-data="{ submitting: false }"
            @submit="submitting = true"
            class="space-y-5"
        >
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-input
                    name="kode_barang"
                    label="Kode Barang"
                    :value="old('kode_barang')"
                    :placeholder="$nextKode"
                    help="Kosongkan untuk dibuat otomatis ({{ $nextKode }})."
                />

                <x-input
                    name="nama"
                    label="Nama Barang"
                    :value="old('nama')"
                    required
                    autofocus
                    placeholder="Contoh: Kertas HVS A4 80gsm"
                />

                <x-select name="category_id" label="Kategori" required>
                    <option value="">Pilih kategori…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                            {{ $category->nama }}
                        </option>
                    @endforeach
                </x-select>

                <x-select name="unit_id" label="Satuan" required>
                    <option value="">Pilih satuan…</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>
                            {{ $unit->nama }} ({{ $unit->singkatan }})
                        </option>
                    @endforeach
                </x-select>

                <x-input
                    name="harga_beli"
                    type="number"
                    min="0"
                    step="1"
                    label="Harga Beli Referensi (Rp)"
                    :value="old('harga_beli', 0)"
                    help="Harga referensi dalam Rupiah. Kosongkan jika 0."
                />

                <x-input
                    name="stok_minimum"
                    type="number"
                    min="0"
                    step="1"
                    label="Stok Minimum"
                    :value="old('stok_minimum', 5)"
                    required
                    help="Sistem memberi peringatan jika stok kurang dari atau sama dengan nilai ini."
                />

                <x-input
                    name="lokasi_rak"
                    label="Lokasi Rak"
                    :value="old('lokasi_rak')"
                    placeholder="Contoh: A-01"
                />

                <div
                    x-data="{
                        previewUrl: null,
                        handleFileChange(event) {
                            if (this.previewUrl) {
                                URL.revokeObjectURL(this.previewUrl);
                            }
                            const file = event.target.files[0];
                            this.previewUrl = file ? URL.createObjectURL(file) : null;
                        }
                    }"
                >
                    <x-label for="foto" value="Foto Barang" />
                    <div class="flex items-center gap-3 mb-2">
                        <template x-if="previewUrl">
                            <img
                                :src="previewUrl"
                                alt="Pratinjau foto barang"
                                class="h-12 w-12 rounded-lg object-cover bg-slate-100 border border-line shrink-0"
                            />
                        </template>
                        <div
                            x-show="!previewUrl"
                            class="h-12 w-12 rounded-lg bg-slate-100 text-subtle grid place-items-center border border-line/60 shrink-0"
                        >
                            <x-icon name="photo" class="w-6 h-6" />
                        </div>
                        <span
                            class="text-xs text-subtle"
                            x-text="previewUrl ? 'Pratinjau foto yang akan diunggah.' : 'Belum ada foto dipilih.'"
                        >Belum ada foto dipilih.</span>
                    </div>
                    <input
                        id="foto"
                        name="foto"
                        type="file"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        @change="handleFileChange($event)"
                        class="block w-full text-sm text-muted file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-line rounded-lg bg-white"
                    />
                    <p class="text-xs text-subtle mt-1">Format JPG/PNG, ukuran maksimal 2 MB.</p>
                    <x-input-error :messages="$errors->get('foto')" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="deskripsi" value="Deskripsi" />
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="3"
                        placeholder="Spesifikasi atau keterangan tambahan barang (opsional)…"
                        class="w-full rounded-lg border {{ $errors->has('deskripsi') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white p-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                    >{{ old('deskripsi') }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi')" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-line">
                <x-button :href="route('products.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary" ::disabled="submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan Barang'">Simpan Barang</span>
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>

