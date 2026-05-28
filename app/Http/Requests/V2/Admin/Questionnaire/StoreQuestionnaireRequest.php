<?php

namespace App\Http\Requests\V2\Admin\Questionnaire;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionnaireRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published,closed'],
            'target_respondent' => ['required', 'string', 'in:all,mahasiswa,dosen,pegawai,dosen_pegawai'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.order' => ['required', 'integer'],
            'sections.*.questions' => ['required', 'array', 'min:1'],
            'sections.*.questions.*.id' => ['nullable', 'integer'],
            'sections.*.questions.*.question_text' => ['required', 'string'],
            'sections.*.questions.*.question_type' => ['required', 'string', 'in:text,paragraph,radio,checkbox,select'],
            'sections.*.questions.*.options' => ['nullable', 'array'],
            'sections.*.questions.*.options.*' => ['required', 'string'],
            'sections.*.questions.*.is_required' => ['required', 'boolean'],
            'sections.*.questions.*.order' => ['required', 'integer'],
        ];
    }
}
