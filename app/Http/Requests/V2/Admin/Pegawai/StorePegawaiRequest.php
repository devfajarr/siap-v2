<?php

namespace App\Http\Requests\V2\Admin\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePegawaiRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'nuptk' => [
                'nullable',
                'numeric',
                'digits:12',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'jenis_kelamin' => 'required|in:Laki-Laki,Perempuan',
            'no_telephone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'agama' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('pegawais')->whereNull('deleted_at'),
            ],
            'password' => 'required|min:6'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nuptk.digits' => 'NUPTK harus terdiri dari 12 digit.',
            'nuptk.unique' => 'NUPTK sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'no_telephone.required' => 'Nomor WhatsApp wajib diisi.',
            'no_telephone.unique' => 'Nomor WhatsApp sudah terdaftar.',
            'agama.required' => 'Agama wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
