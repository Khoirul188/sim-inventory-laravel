<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('harga_beli') === null || $this->input('harga_beli') === '') {
            $this->merge(['harga_beli' => 0]);
        }

        if ($this->has('kode_barang') && trim((string) $this->input('kode_barang')) === '') {
            $this->merge(['kode_barang' => null]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'kode_barang' => [
                'nullable',
                'string',
                'max:30',
                // Keunikan juga berlaku untuk barang yang sudah di-soft delete (PRD §7.4)
                Rule::unique('products', 'kode_barang')->ignore($productId),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'harga_beli' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'stok_minimum' => ['required', 'integer', 'min:0', 'max:1000000'],
            'lokasi_rak' => ['nullable', 'string', 'max:50'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barang.unique' => 'Kode barang sudah digunakan.',
            'kode_barang.max' => 'Kode barang maksimal 30 karakter.',
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.max' => 'Nama barang maksimal 150 karakter.',
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori barang tidak ditemukan.',
            'unit_id.required' => 'Satuan barang wajib dipilih.',
            'unit_id.exists' => 'Satuan barang tidak ditemukan.',
            'harga_beli.numeric' => 'Harga beli harus berupa angka.',
            'harga_beli.min' => 'Harga beli tidak boleh kurang dari 0.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
            'stok_minimum.integer' => 'Stok minimum harus berupa bilangan bulat.',
            'stok_minimum.min' => 'Stok minimum tidak boleh kurang dari 0.',
            'lokasi_rak.max' => 'Lokasi rak maksimal 50 karakter.',
            'foto.image' => 'Foto harus berformat JPG atau PNG dengan ukuran maksimal 2 MB.',
            'foto.mimes' => 'Foto harus berformat JPG atau PNG dengan ukuran maksimal 2 MB.',
            'foto.max' => 'Foto harus berformat JPG atau PNG dengan ukuran maksimal 2 MB.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
