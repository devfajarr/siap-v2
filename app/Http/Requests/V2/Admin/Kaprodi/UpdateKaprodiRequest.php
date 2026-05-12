<?php

namespace App\Http\Requests\V2\Admin\Kaprodi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKaprodiRequest extends FormRequest
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
            'dosens_id' => 'required|exists:dosens,id',
            'prodis_id' => [
                'required',
                'exists:prodi,id',
                Rule::unique('kaprodi')->where(function ($query) {
                    return $query->where('dosens_id', $this->dosens_id);
                })->ignore($this->route('data_kaprodi'))
            ],
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
            'dosens_id.required' => 'Dosen wajib dipilih.',
            'dosens_id.exists' => 'Dosen tidak valid.',
            'prodis_id.required' => 'Program studi wajib dipilih.',
            'prodis_id.exists' => 'Program studi tidak valid.',
            'prodis_id.unique' => 'Dosen ini sudah menjabat sebagai Kaprodi di prodi tersebut.',
            'status.required' => 'Status wajib dipilih.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
