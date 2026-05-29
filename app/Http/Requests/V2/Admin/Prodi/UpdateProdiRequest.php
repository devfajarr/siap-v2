<?php

namespace App\Http\Requests\V2\Admin\Prodi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode_prodi' => [
                'required',
                'string',
                'max:50',
                Rule::unique('prodi')->ignore($this->route('data_prodi'))->whereNull('deleted_at'),
            ],
            'nama_prodi' => 'required|string|max:255',
            'singkatan' => 'required|string|max:50',
            'jenjang' => 'required|string|max:50',
            'alias_nama' => 'required|string|max:255',
            'alias_jenjang' => 'required|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'kode_prodi.required' => 'Kode prodi wajib diisi.',
            'kode_prodi.unique' => 'Kode prodi sudah terdaftar.',
            'nama_prodi.required' => 'Nama prodi wajib diisi.',
            'singkatan.required' => 'Singkatan wajib diisi.',
            'jenjang.required' => 'Jenjang wajib diisi.',
            'alias_nama.required' => 'Nama alias wajib diisi.',
            'alias_jenjang.required' => 'Jenjang alias wajib diisi.',
        ];
    }
}
