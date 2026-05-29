<?php

namespace App\Http\Requests\V2\Admin\Matkul;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMatkulRequest extends FormRequest
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
            'nama_matkul' => [
                'required',
                'string',
                'max:255',
                Rule::unique('matkuls')->ignore($this->route('data_matkul'))->where(function ($query) {
                    return $query->where('semester_id', $this->semester_id)
                        ->where('prodi_id', $this->prodi_id);
                }),
            ],
            'alias' => 'required|string|max:255',
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('matkuls')->ignore($this->route('data_matkul'))->where(function ($query) {
                    return $query->where('semester_id', $this->semester_id)
                        ->where('prodi_id', $this->prodi_id);
                }),
            ],
            'prodi_id' => 'required|exists:prodi,id',
            'semester_id' => 'required|exists:semesters,id',
            'teori' => 'required|integer|min:0',
            'praktek' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_matkul.required' => 'Nama mata kuliah wajib diisi.',
            'nama_matkul.unique' => 'Nama mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
            'alias.required' => 'Alias wajib diisi.',
            'kode.required' => 'Kode mata kuliah wajib diisi.',
            'kode.unique' => 'Kode mata kuliah sudah terdaftar untuk kombinasi semester dan prodi ini.',
            'prodi_id.required' => 'Program studi wajib dipilih.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'teori.required' => 'SKS Teori wajib diisi.',
            'praktek.required' => 'SKS Praktek wajib diisi.',
        ];
    }
}
