<?php

namespace App\Http\Requests\V2\Admin\Semester;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
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
            'semester' => [
                'required',
                'numeric',
                'max:99',
                Rule::unique('semesters')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'semester.required' => 'Semester harus diisi.',
            'semester.numeric' => 'Semester harus berupa angka.',
            'semester.max' => 'Maksimal 2 digit.',
            'semester.unique' => 'Semester ini sudah ada.',
        ];
    }
}
