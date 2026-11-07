@php
    $productsCatalog = $products->map(fn ($p) => [
        'id' => (string) $p->id,
        'kode' => $p->kode_barang,
        'nama' => $p->nama,
        'stok' => (int) $p->stok,
        'satuan' => $p->unit?->singkatan ?? '',
    ])->values();

    $initialItems = old('items', [
        ['product_id' => '', 'jumlah' => 1],
    ]);
@endphp

<x-app-layout title="Tambah Barang Keluar">
    <x-page-header
        title="Tambah Barang Keluar"
        :breadcrumbs="['Transaksi' => null, 'Barang Keluar' => route('stock-outs.index'), 'Tambah' => null]"
    />

    <form
        method="POST"
        action="{{ route('stock-outs.store') }}"
        x-data="{
            submitting: false,
            alasan: @js(old('alasan', 'pemakaian')),
            catalog: @js($productsCatalog),
            rows: @js(array_values($initialItems)),
            addRow() {
                this.rows.push({ product_id: '', jumlah: 1 });
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            findProduct(productId) {
                return this.catalog.find(p => String(p.id) === String(productId)) || null;
            },
            isProductTaken(productId, currentIndex) {
                if (!productId) return false;
                return this.rows.some((r, idx) => idx !== currentIndex && String(r.product_id) === String(productId));
            },
            isInsufficient(row) {
                const p = this.findProduct(row.product_id);
                if (!p) return false;
                return (Number(row.jumlah) || 0) > p.stok;
            }
        }"
        @submit="submitting = true"
        class="space-y-6 max-w-5xl"
    >
        @csrf

        <!-- Kartu Header Transaksi (DESIGN §7 & PRD §14.5f) -->
        <x-card>
            <h2 class="text-base font-semibold text-ink mb-4">Informasi Pengeluaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <x-label value="No. Transaksi (Otomatis)" />
                    <div class="h-10 px-3.5 rounded-lg border border-line bg-slate-50 flex items-center text-sm font-semibold text-ink tabular-nums">
                        {{ $nextNo }}
                    </div>
                    <p class="text-xs text-subtle mt-1">Nomor final ditetapkan saat transaksi disimpan.</p>
                </div>

                <x-input
                    name="tanggal"
                    type="date"
                    label="Tanggal Keluar"
                    :value="old('tanggal', now()->toDateString())"
                    max="{{ now()->toDateString() }}"
                    required
                />

                <div>
                    <x-label for="alasan" value="Alasan Pengeluaran" :required="true" />
                    <select
                        id="alasan"
                        name="alasan"
                        x-model="alasan"
                        required
                        class="h-10 w-full rounded-lg border border-line bg-white px-3.5 text-sm text-ink focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                    >
                        @foreach ($alasanOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('alasan')" />
                </div>

                <x-input
                    name="tujuan"
                    label="Tujuan / Penerima"
                    :value="old('tujuan')"
                    placeholder="Contoh: Bagian Keuangan / Divisi IT"
                />

                <div class="md:col-span-2">
                    <label for="keterangan" class="block text-xs font-medium text-ink mb-1.5">
                        Keterangan
                        <span x-show="alasan === 'koreksi' || alasan === 'lainnya'" class="text-danger ml-0.5">*</span>
                    </label>
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="2"
                        :required="alasan === 'koreksi' || alasan === 'lainnya'"
                        placeholder="Wajib diisi bila alasan Koreksi atau Lainnya (sebutkan nomor transaksi yang dikoreksi atau alasan rinci)…"
                        class="w-full rounded-lg border {{ $errors->has('keterangan') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white p-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                    >{{ old('keterangan') }}</textarea>
                    <x-input-error :messages="$errors->get('keterangan')" />
                </div>
            </div>
        </x-card>

        <!-- Kartu Daftar Item Barang Keluar (Baris Dinamis Alpine.js) -->
        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 md:p-6 border-b border-line flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Rincian Barang Keluar</h2>
                    <p class="text-xs text-subtle mt-0.5">Pilih barang dan masukkan jumlah keluar. Jumlah tidak boleh melebihi stok tersedia.</p>
                </div>
                <x-button type="button" variant="secondary" @click="addRow()">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Tambah Baris</span>
                </x-button>
            </div>

            @if ($errors->has('items'))
                <div class="px-5 pt-4">
                    <x-input-error :messages="$errors->get('items')" />
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-subtle border-b border-line">
                        <tr>
                            <th class="px-5 py-3 text-left min-w-[280px]">Barang <span class="text-danger">*</span></th>
                            <th class="px-4 py-3 text-right w-40">Tersedia</th>
                            <th class="px-4 py-3 text-right w-40">Jumlah Keluar <span class="text-danger">*</span></th>
                            <th class="px-4 py-3 text-center w-14"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="align-top hover:bg-slate-50/50">
                                <td class="px-5 py-3.5">
                                    <label :for="'item-product-' + index" class="sr-only">Pilih Barang</label>
                                    <select
                                        :id="'item-product-' + index"
                                        :name="'items[' + index + '][product_id]'"
                                        x-model="row.product_id"
                                        required
                                        class="h-10 w-full rounded-lg border border-line bg-white px-3 text-sm text-ink focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                                    >
                                        <option value="">Pilih barang…</option>
                                        <template x-for="product in catalog" :key="product.id">
                                            <option
                                                :value="product.id"
                                                :disabled="isProductTaken(product.id, index)"
                                                x-show="!isProductTaken(product.id, index) || String(row.product_id) === String(product.id)"
                                                x-text="product.kode + ' — ' + product.nama + ' (Tersedia: ' + product.stok + ' ' + product.satuan + ')'"
                                            ></option>
                                        </template>
                                    </select>
                                    <p
                                        x-show="isInsufficient(row)"
                                        class="text-xs text-danger mt-1.5 font-medium"
                                        x-text="'⚠ Jumlah melebihi stok tersedia (' + (findProduct(row.product_id)?.stok ?? 0) + ' ' + (findProduct(row.product_id)?.satuan ?? '') + ').'"
                                    ></p>
                                </td>

                                <td class="px-4 py-3.5 text-right tabular-nums whitespace-nowrap pt-5">
                                    <template x-if="findProduct(row.product_id)">
                                        <span
                                            class="font-semibold"
                                            :class="findProduct(row.product_id).stok === 0 ? 'text-danger-ink' : 'text-ink'"
                                            x-text="findProduct(row.product_id).stok.toLocaleString('id-ID') + ' ' + findProduct(row.product_id).satuan"
                                        ></span>
                                    </template>
                                    <template x-if="!findProduct(row.product_id)">
                                        <span class="text-subtle">—</span>
                                    </template>
                                </td>

                                <td class="px-4 py-3.5">
                                    <label :for="'item-jumlah-' + index" class="sr-only">Jumlah Keluar</label>
                                    <input
                                        :id="'item-jumlah-' + index"
                                        type="number"
                                        min="1"
                                        step="1"
                                        :name="'items[' + index + '][jumlah]'"
                                        x-model="row.jumlah"
                                        required
                                        :class="isInsufficient(row) ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20'"
                                        class="h-10 w-full rounded-lg border bg-white px-3 text-right text-sm text-ink tabular-nums focus:ring-2"
                                    />
                                </td>

                                <td class="px-4 py-3.5 text-center">
                                    <button
                                        type="button"
                                        @click="removeRow(index)"
                                        :disabled="rows.length === 1"
                                        :aria-label="'Hapus baris ' + (index + 1)"
                                        class="h-10 w-10 inline-flex items-center justify-center rounded-lg text-muted hover:bg-slate-100 hover:text-danger disabled:opacity-30 disabled:pointer-events-none transition-colors duration-150"
                                    >
                                        <x-icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            @if ($errors->any())
                <div class="px-5 py-3 bg-danger-soft/40 border-t border-line">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-danger-ink">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="px-5 py-4 bg-slate-50/60 border-t border-line flex items-center justify-between">
                <x-button type="button" variant="secondary" @click="addRow()">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Tambah Baris</span>
                </x-button>
            </div>
        </x-card>

        <div class="flex items-center justify-end gap-3">
            <x-button :href="route('stock-outs.index')" variant="secondary">Batal</x-button>
            <x-button type="submit" variant="primary" ::disabled="submitting">
                <span x-text="submitting ? 'Menyimpan…' : 'Simpan Transaksi'">Simpan Transaksi</span>
            </x-button>
        </div>
    </form>
</x-app-layout>

