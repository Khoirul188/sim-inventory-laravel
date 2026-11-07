<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')?->id;

        return [
            'nama' => ['required', 'string', 'max:50'],
            'singkatan' => [
                'required',
                'string',
                'max:10',
                Rule::unique('units', 'singkatan')->ignore($unitId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.max' => 'Nama satuan maksimal 50 karakter.',
            'singkatan.required' => 'Singkatan satuan wajib diisi.',
            'singkatan.max' => 'Singkatan satuan maksimal 10 karakter.',
            'singkatan.unique' => 'Singkatan satuan sudah digunakan.',
        ];
    }
}
