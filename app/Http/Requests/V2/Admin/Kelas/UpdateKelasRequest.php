<?php

namespace App\Http\Requests\V2\Admin\Kelas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelasRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_prodi' => 'required|exists:prodi,id',
            'id_semester' => 'required|exists:semesters,id',
            'jenis_kelas' => 'required|in:Reguler,Non Regular',
            'kode_kelas' => [
                'required',
                'string',
                Rule::unique('kelas', 'kode_kelas')->ignore($this->route('data_kela'))->whereNull('deleted_at')
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id_prodi.required' => 'Prodi harus dipilih.',
            'id_prodi.exists' => 'Prodi tidak valid.',
            'id_semester.required' => 'Semester harus dipilih.',
            'id_semester.exists' => 'Semester tidak valid.',
            'jenis_kelas.required' => 'Jenis kelas harus dipilih.',
            'jenis_kelas.in' => 'Jenis kelas tidak valid.',
            'kode_kelas.required' => 'Kode kelas harus diisi.',
            'kode_kelas.unique' => 'Kode kelas sudah terdaftar.',
        ];
    }
}
