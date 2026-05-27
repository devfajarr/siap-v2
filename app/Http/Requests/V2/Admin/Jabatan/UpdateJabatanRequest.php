<?php

namespace App\Http\Requests\V2\Admin\Jabatan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:0,1',
            'password' => 'nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
