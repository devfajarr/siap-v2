<?php

namespace App\Exports;

use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionnaireResponseExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $questionnaire;

    protected $questions;

    public function __construct(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $this->questions = $questionnaire->questions()->orderBy('order')->get();
    }

    /**
     * Return collection of responses.
     */
    public function collection()
    {
        return QuestionnaireResponse::with(['answers', 'respondent', 'dosen', 'matkul'])
            ->where('questionnaire_id', $this->questionnaire->id)
            ->latest()
            ->get();
    }

    /**
     * Excel columns header.
     */
    public function headings(): array
    {
        $headers = [
            'No',
            'Waktu Pengisian',
            'Identitas Responden',
            'Tipe Responden',
            'IP Address',
        ];

        if ($this->questionnaire->type === 'kinerja_pengajar') {
            array_splice($headers, 3, 0, ['Dosen yang Dinilai', 'Mata Kuliah']);
        }

        foreach ($this->questions as $question) {
            $headers[] = $question->question_text;
        }

        return $headers;
    }

    /**
     * Map each row data.
     *
     * @param  QuestionnaireResponse  $response
     */
    public function map($response): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $isKinerjaDosen = $this->questionnaire->type === 'kinerja_pengajar';

        $respondentName = 'Anonim';
        $respondentType = 'Tamu / Umum';

        if ($response->respondent) {
            if ($isKinerjaDosen) {
                $respondentName = 'Anonim';
            } else {
                $res = $response->respondent;
                $respondentName = $res->nama ?? $res->nama_lengkap ?? $res->name ?? 'User';
            }
            $respondentType = class_basename($response->respondent_type);
        }

        $rowData = [
            $rowNumber,
            $response->submitted_at ? $response->submitted_at->format('Y-m-d H:i:s') : '-',
            $respondentName,
        ];

        if ($isKinerjaDosen) {
            $rowData[] = $response->dosen->nama ?? '-';
            $rowData[] = $response->matkul->nama_matkul ?? '-';
        }

        $rowData[] = $respondentType;
        $rowData[] = $response->ip_address ?? '-';

        $answersMap = $response->answers->keyBy('question_id');

        foreach ($this->questions as $question) {
            $answer = $answersMap->get($question->id);
            if ($answer) {
                if ($question->question_type === 'checkbox') {
                    $decoded = json_decode($answer->answer_value, true);
                    $rowData[] = is_array($decoded) ? implode(', ', $decoded) : $answer->answer_value;
                } else {
                    $rowData[] = $answer->answer_value;
                }
            } else {
                $rowData[] = '-';
            }
        }

        return $rowData;
    }

    /**
     * Stylize the header row.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
