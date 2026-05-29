<?php

namespace App\Http\Requests\V2\Admin\TahunAkademik;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTahunAkademikRequest extends FormRequest
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
            'tahun_akademik' => [
                'required',
                'regex:/^[0-9]{4}\/[0-9]{4}$/',
            ],
            'status' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tahun_akademik.required' => 'Tahun akademik wajib diisi.',
            'tahun_akademik.regex' => 'Format tahun akademik tidak valid [YYYY/YYYY].',
            'status.required' => 'Status wajib dipilih.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->tahun_akademik) {
                $tahun = explode('/', $this->tahun_akademik);
                if (count($tahun) === 2) {
                    $tahunPertama = (int) $tahun[0];
                    $tahunKedua = (int) $tahun[1];

                    if ($tahunKedua <= $tahunPertama) {
                        $validator->errors()->add('tahun_akademik', 'Tahun kedua harus lebih besar dari tahun pertama.');
                    }
                }
            }
        });
    }
}
