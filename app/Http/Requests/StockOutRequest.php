<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'alasan' => ['required', Rule::in(['pemakaian', 'penjualan', 'rusak', 'koreksi', 'lainnya'])],
            'tujuan' => ['required_if:alasan,pemakaian,penjualan', 'nullable', 'string', 'max:150'],
            'keterangan' => ['required_if:alasan,koreksi,lainnya', 'nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    /**
     * Cek kecukupan stok awal pada tahap validasi form sebelum masuk ke StockService (PRD §7.7, §9.5).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            $productIds = collect($items)->pluck('product_id')->filter()->unique()->values();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($items as $index => $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['jumlah'] ?? 0);
                $product = $products->get($productId);

                if ($product && $qty > $product->stok) {
                    $validator->errors()->add(
                        "items.{$index}.jumlah",
                        "Stok {$product->nama} tidak cukup. Tersedia {$product->stok}, diminta {$qty}."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal transaksi tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'alasan.required' => 'Alasan wajib dipilih.',
            'alasan.in' => 'Alasan pengeluaran tidak valid.',
            'tujuan.required_if' => 'Tujuan / penerima wajib diisi untuk alasan ini.',
            'tujuan.max' => 'Tujuan maksimal 150 karakter.',
            'keterangan.required_if' => 'Keterangan wajib diisi untuk alasan ini.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'items.required' => 'Transaksi minimal harus memiliki satu baris barang.',
            'items.array' => 'Format daftar barang tidak valid.',
            'items.min' => 'Transaksi minimal harus memiliki satu baris barang.',
            'items.*.product_id.required' => 'Barang wajib dipilih.',
            'items.*.product_id.distinct' => 'Barang yang sama tidak boleh dimasukkan dua kali. Gabungkan jumlahnya.',
            'items.*.product_id.exists' => 'Barang tidak ditemukan.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah harus bilangan bulat lebih dari 0.',
            'items.*.jumlah.min' => 'Jumlah harus bilangan bulat lebih dari 0.',
            'items.*.jumlah.max' => 'Jumlah maksimal 1.000.000.',
        ];
    }
}
