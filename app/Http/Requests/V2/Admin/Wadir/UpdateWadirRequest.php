<?php

namespace App\Http\Requests\V2\Admin\Wadir;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWadirRequest extends FormRequest
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
            'status' => 'required|in:0,1',
            'no' => [
                'required',
                'numeric',
                Rule::unique('wadirs')->ignore($this->route('data_wadir')),
            ],
            'password' => 'nullable|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib dipilih.',
            'no.required' => 'Posisi/Nomor Wadir wajib diisi.',
            'no.unique' => 'Posisi Wakil Direktur ini sudah terisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
