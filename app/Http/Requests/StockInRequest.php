<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.harga_beli' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal transaksi tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
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
            'items.*.harga_beli.numeric' => 'Harga beli harus berupa angka.',
            'items.*.harga_beli.min' => 'Harga beli tidak boleh bernilai negatif.',
        ];
    }
}

