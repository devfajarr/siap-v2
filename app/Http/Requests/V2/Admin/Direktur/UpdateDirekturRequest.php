<?php

namespace App\Http\Requests\V2\Admin\Direktur;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDirekturRequest extends FormRequest
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
            'status' => 'required|in:0,1',
            'password' => 'nullable|min:6'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib dipilih.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
