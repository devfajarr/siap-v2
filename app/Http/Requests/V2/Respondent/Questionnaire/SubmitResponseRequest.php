<?php

namespace App\Http\Requests\V2\Respondent\Questionnaire;

use App\Models\Questionnaire;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitResponseRequest extends FormRequest
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
        $rules = [
            'answers' => ['required', 'array'],
        ];

        // Cari kuisioner berdasarkan ID di parameter route
        $questionnaire = Questionnaire::find($this->route('id'));

        if ($questionnaire && $questionnaire->type === 'kinerja_pengajar') {
            $rules['dosen_id'] = ['required', 'exists:dosens,id'];
            $rules['matkul_id'] = ['required', 'exists:matkuls,id'];
            $rules['jadwal_id'] = ['required', 'exists:jadwals,id'];
        } else {
            $rules['dosen_id'] = ['nullable'];
            $rules['matkul_id'] = ['nullable'];
            $rules['jadwal_id'] = ['nullable'];
        }

        return $rules;
    }
}
