<?php

namespace App\Http\Requests\V2\Admin\Wadir;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWadirRequest extends FormRequest
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
            'dosens_id' => 'required|exists:dosens,id|unique:wadirs,dosens_id',
            'no' => [
                'required',
                'numeric',
                Rule::unique('wadirs'),
            ],
            'password_mode' => 'required|in:dosen,existing,custom',
            'password' => 'required_if:password_mode,custom|nullable|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dosens_id.required' => 'Dosen wajib dipilih.',
            'dosens_id.unique' => 'Dosen ini sudah terdaftar sebagai Wakil Direktur.',
            'no.required' => 'Posisi/Nomor Wadir wajib diisi.',
            'no.unique' => 'Posisi Wakil Direktur ini sudah terisi.',
            'password_mode.required' => 'Mode password wajib dipilih.',
            'password.required_if' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
