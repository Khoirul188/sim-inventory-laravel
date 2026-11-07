@php
    $productsCatalog = $products->map(fn ($p) => [
        'id' => (string) $p->id,
        'kode' => $p->kode_barang,
        'nama' => $p->nama,
        'harga_beli' => (float) $p->harga_beli,
        'stok' => (int) $p->stok,
        'satuan' => $p->unit?->singkatan ?? '',
    ])->values();

    $initialItems = old('items', [
        ['product_id' => '', 'jumlah' => 1, 'harga_beli' => ''],
    ]);
@endphp

<x-app-layout title="Tambah Barang Masuk">
    <x-page-header
        title="Tambah Barang Masuk"
        :breadcrumbs="['Transaksi' => null, 'Barang Masuk' => route('stock-ins.index'), 'Tambah' => null]"
    />

    <form
        method="POST"
        action="{{ route('stock-ins.store') }}"
        x-data="{
            submitting: false,
            catalog: @js($productsCatalog),
            rows: @js(array_values($initialItems)),
            addRow() {
                this.rows.push({ product_id: '', jumlah: 1, harga_beli: '' });
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            findProduct(productId) {
                return this.catalog.find(p => String(p.id) === String(productId)) || null;
            },
            onProductChange(index) {
                const p = this.findProduct(this.rows[index].product_id);
                if (p) {
                    this.rows[index].harga_beli = p.harga_beli;
                }
            },
            isProductTaken(productId, currentIndex) {
                if (!productId) return false;
                return this.rows.some((r, idx) => idx !== currentIndex && String(r.product_id) === String(productId));
            },
            rowSubtotal(row) {
                const qty = Number(row.jumlah) || 0;
                const price = row.harga_beli !== '' && row.harga_beli !== null
                    ? Number(row.harga_beli)
                    : (this.findProduct(row.product_id)?.harga_beli || 0);
                return Math.max(0, qty * price);
            },
            grandTotal() {
                return this.rows.reduce((sum, row) => sum + this.rowSubtotal(row), 0);
            },
            formatRupiah(val) {
                return 'Rp ' + Math.round(val || 0).toLocaleString('id-ID');
            }
        }"
        @submit="submitting = true"
        class="space-y-6 max-w-5xl"
    >
        @csrf

        <!-- Kartu Header Transaksi (DESIGN §7 & PRD §14.5e) -->
        <x-card>
            <h2 class="text-base font-semibold text-ink mb-4">Informasi Penerimaan</h2>
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
                    label="Tanggal Masuk"
                    :value="old('tanggal', now()->toDateString())"
                    max="{{ now()->toDateString() }}"
                    required
                />

                <x-select name="supplier_id" label="Supplier">
                    <option value="">— Tanpa Supplier / Umum —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->nama }}
                        </option>
                    @endforeach
                </x-select>

                <div class="md:col-span-3">
                    <x-label for="keterangan" value="Keterangan" />
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="2"
                        placeholder="Nomor surat jalan, nomor PO, atau catatan penerimaan (opsional)…"
                        class="w-full rounded-lg border {{ $errors->has('keterangan') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white p-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                    >{{ old('keterangan') }}</textarea>
                    <x-input-error :messages="$errors->get('keterangan')" />
                </div>
            </div>
        </x-card>

        <!-- Kartu Daftar Item Barang (Baris Dinamis Alpine.js) -->
        <x-card class="!p-0 overflow-hidden">
            <div class="p-5 md:p-6 border-b border-line flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Rincian Barang Masuk</h2>
                    <p class="text-xs text-subtle mt-0.5">Pilih barang dan isi jumlah yang diterima. Barang tidak boleh duplikat dalam satu transaksi.</p>
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
                            <th class="px-5 py-3 text-left min-w-[260px]">Barang <span class="text-danger">*</span></th>
                            <th class="px-4 py-3 text-right w-36">Jumlah <span class="text-danger">*</span></th>
                            <th class="px-4 py-3 text-right w-48">Harga Beli (Rp)</th>
                            <th class="px-4 py-3 text-right w-44">Subtotal</th>
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
                                        @change="onProductChange(index)"
                                        required
                                        class="h-10 w-full rounded-lg border border-line bg-white px-3 text-sm text-ink focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                                    >
                                        <option value="">Pilih barang…</option>
                                        <template x-for="product in catalog" :key="product.id">
                                            <option
                                                :value="product.id"
                                                :disabled="isProductTaken(product.id, index)"
                                                x-show="!isProductTaken(product.id, index) || String(row.product_id) === String(product.id)"
                                                x-text="product.kode + ' — ' + product.nama + ' (stok ' + product.stok + ' ' + product.satuan + ')'"
                                            ></option>
                                        </template>
                                    </select>
                                </td>

                                <td class="px-4 py-3.5">
                                    <label :for="'item-jumlah-' + index" class="sr-only">Jumlah</label>
                                    <div class="relative flex items-center">
                                        <input
                                            :id="'item-jumlah-' + index"
                                            type="number"
                                            min="1"
                                            step="1"
                                            :name="'items[' + index + '][jumlah]'"
                                            x-model="row.jumlah"
                                            required
                                            class="h-10 w-full rounded-lg border border-line bg-white px-3 text-right text-sm text-ink tabular-nums focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                                        />
                                    </div>
                                    <p
                                        class="text-xs text-subtle text-right mt-1"
                                        x-show="findProduct(row.product_id)"
                                        x-text="'Satuan: ' + (findProduct(row.product_id)?.satuan || '')"
                                    ></p>
                                </td>

                                <td class="px-4 py-3.5">
                                    <label :for="'item-harga-' + index" class="sr-only">Harga Beli</label>
                                    <input
                                        :id="'item-harga-' + index"
                                        type="number"
                                        min="0"
                                        step="1"
                                        :name="'items[' + index + '][harga_beli]'"
                                        x-model="row.harga_beli"
                                        placeholder="Harga referensi"
                                        class="h-10 w-full rounded-lg border border-line bg-white px-3 text-right text-sm text-ink tabular-nums focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                                    />
                                </td>

                                <td class="px-4 py-3.5 text-right font-semibold text-ink tabular-nums whitespace-nowrap pt-5">
                                    <span x-text="formatRupiah(rowSubtotal(row))"></span>
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

            <div class="px-5 py-4 bg-slate-50/60 border-t border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <x-button type="button" variant="secondary" @click="addRow()">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Tambah Baris</span>
                </x-button>

                <div class="flex items-center justify-end gap-3">
                    <span class="text-sm font-medium text-muted">Total Nilai Masuk:</span>
                    <span class="text-lg font-bold text-ink tabular-nums" x-text="formatRupiah(grandTotal())">Rp 0</span>
                </div>
            </div>
        </x-card>

        <div class="flex items-center justify-end gap-3">
            <x-button :href="route('stock-ins.index')" variant="secondary">Batal</x-button>
            <x-button type="submit" variant="primary" ::disabled="submitting">
                <span x-text="submitting ? 'Menyimpan…' : 'Simpan Transaksi'">Simpan Transaksi</span>
            </x-button>
        </div>
    </form>
</x-app-layout>

