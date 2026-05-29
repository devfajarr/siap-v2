<?php

namespace App\Http\Requests\V2\Admin\Kaprodi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreKaprodiRequest extends FormRequest
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
            'dosens_id' => 'required|exists:dosens,id',
            'prodis_id' => [
                'required',
                'exists:prodi,id',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('kaprodi_prodi')
                        ->join('kaprodi', 'kaprodi_prodi.kaprodi_id', '=', 'kaprodi.id')
                        ->where('kaprodi.dosens_id', $this->dosens_id)
                        ->where('kaprodi_prodi.prodi_id', $value)
                        ->exists();

                    if ($exists) {
                        $fail('Dosen ini sudah menjabat sebagai Kaprodi di prodi tersebut.');
                    }
                },
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
            'dosens_id.exists' => 'Dosen tidak valid.',
            'prodis_id.required' => 'Program studi wajib dipilih.',
            'prodis_id.exists' => 'Program studi tidak valid.',
            'prodis_id.unique' => 'Dosen ini sudah menjabat sebagai Kaprodi di prodi tersebut.',
            'password_mode.required' => 'Mode password wajib dipilih.',
            'password.required_if' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
