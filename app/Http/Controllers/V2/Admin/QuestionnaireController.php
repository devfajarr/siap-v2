<?php

namespace App\Http\Controllers\V2\Admin;

use App\Exports\QuestionnaireResponseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Admin\Questionnaire\StoreQuestionnaireRequest;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Pegawai;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\QuestionnaireSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class QuestionnaireController extends Controller
{
    /**
     * Map URL parameter to database enum type.
     */
    protected function getDatabaseType(string $type): string
    {
        $map = [
            'pelayanan' => 'pelayanan',
            'kinerja-pengajar' => 'kinerja_pengajar',
            'ami' => 'ami',
        ];

        if (! array_key_exists($type, $map)) {
            abort(404, 'Kategori kuisioner tidak valid.');
        }

        return $map[$type];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $type)
    {
        $dbType = $this->getDatabaseType($type);
        $search = $request->input('search');

        $questionnaires = Questionnaire::query()
            ->where('type', $dbType)
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->with('createdBy')
            ->withCount('responses')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Questionnaire/Index', [
            'questionnaires' => $questionnaires,
            'category' => $type, // 'pelayanan', 'kinerja-pengajar', 'ami'
            'categoryName' => match ($type) {
                'pelayanan' => 'Kuis Pelayanan',
                'kinerja-pengajar' => 'Kinerja Pengajar',
                'ami' => 'Kuisioner AMI',
            },
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $type)
    {
        $this->getDatabaseType($type); // Validasi tipe saja

        return Inertia::render('Admin/Questionnaire/Create', [
            'category' => $type,
            'categoryName' => match ($type) {
                'pelayanan' => 'Kuis Pelayanan',
                'kinerja-pengajar' => 'Kinerja Pengajar',
                'ami' => 'Kuisioner AMI',
            },
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionnaireRequest $request, string $type)
    {
        $dbType = $this->getDatabaseType($type);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $dbType) {
            // Buat Kuisioner
            $questionnaire = Questionnaire::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $dbType,
                'status' => $validated['status'],
                'target_respondent' => $validated['target_respondent'],
                'created_by_id' => auth()->id(),
                'created_by_type' => get_class(auth()->user()),
            ]);

            // Buat Sections & Questions
            foreach ($validated['sections'] as $sData) {
                $section = $questionnaire->sections()->create([
                    'title' => $sData['title'],
                    'description' => $sData['description'] ?? null,
                    'order' => $sData['order'],
                ]);

                foreach ($sData['questions'] as $qData) {
                    $section->questions()->create([
                        'questionnaire_id' => $questionnaire->id, // Tetap diisi untuk query relasi langsung
                        'question_text' => $qData['question_text'],
                        'question_type' => $qData['question_type'],
                        'options' => $qData['options'] ?? null,
                        'is_required' => $qData['is_required'],
                        'order' => $qData['order'],
                    ]);
                }
            }
        });

        return redirect()->route('v2.admin.kuisioner.index', $type)
            ->with('success', 'Kuisioner berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $type, int $id)
    {
        $this->getDatabaseType($type); // Validasi tipe
        $questionnaire = Questionnaire::with('sections.questions')->findOrFail($id);

        return Inertia::render('Admin/Questionnaire/Create', [
            'category' => $type,
            'categoryName' => match ($type) {
                'pelayanan' => 'Kuis Pelayanan',
                'kinerja-pengajar' => 'Kinerja Pengajar',
                'ami' => 'Kuisioner AMI',
            },
            'questionnaire' => $questionnaire,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreQuestionnaireRequest $request, string $type, int $id)
    {
        $dbType = $this->getDatabaseType($type);
        $questionnaire = Questionnaire::findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $questionnaire) {
            // Update Kuisioner
            $questionnaire->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'target_respondent' => $validated['target_respondent'],
            ]);

            // 1. Sinkronisasi Seksi (Sections)
            $requestSectionIds = collect($validated['sections'])->pluck('id')->filter()->toArray();
            $questionnaire->sections()->whereNotIn('id', $requestSectionIds)->delete();

            // 2. Kumpulkan ID pertanyaan yang dikirim di request
            $requestQuestionIds = [];
            foreach ($validated['sections'] as $sData) {
                if (isset($sData['questions'])) {
                    $requestQuestionIds = array_merge(
                        $requestQuestionIds,
                        collect($sData['questions'])->pluck('id')->filter()->toArray()
                    );
                }
            }

            // Hapus pertanyaan kuis ini yang tidak ada di request
            $questionnaire->questions()->whereNotIn('questions.id', $requestQuestionIds)->delete();

            // 3. Proses simpan/update Seksi dan Pertanyaan
            foreach ($validated['sections'] as $sData) {
                if (isset($sData['id'])) {
                    // Update Seksi
                    QuestionnaireSection::where('id', $sData['id'])->update([
                        'title' => $sData['title'],
                        'description' => $sData['description'] ?? null,
                        'order' => $sData['order'],
                    ]);
                    $section = QuestionnaireSection::find($sData['id']);
                } else {
                    // Buat Seksi baru
                    $section = $questionnaire->sections()->create([
                        'title' => $sData['title'],
                        'description' => $sData['description'] ?? null,
                        'order' => $sData['order'],
                    ]);
                }

                // Proses Pertanyaan di seksi ini
                foreach ($sData['questions'] as $qData) {
                    if (isset($qData['id'])) {
                        // Update Pertanyaan
                        Question::where('id', $qData['id'])->update([
                            'section_id' => $section->id,
                            'question_text' => $qData['question_text'],
                            'question_type' => $qData['question_type'],
                            'options' => $qData['options'] ?? null,
                            'is_required' => $qData['is_required'],
                            'order' => $qData['order'],
                        ]);
                    } else {
                        // Buat Pertanyaan baru
                        $section->questions()->create([
                            'questionnaire_id' => $questionnaire->id,
                            'question_text' => $qData['question_text'],
                            'question_type' => $qData['question_type'],
                            'options' => $qData['options'] ?? null,
                            'is_required' => $qData['is_required'],
                            'order' => $qData['order'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('v2.admin.kuisioner.index', $type)
            ->with('success', 'Kuisioner berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $type, int $id)
    {
        $this->getDatabaseType($type);
        $questionnaire = Questionnaire::findOrFail($id);
        $questionnaire->delete();

        return redirect()->back()->with('success', 'Kuisioner berhasil dihapus.');
    }

    /**
     * Display statistical dashboard of questionnaire responses.
     */
    public function analytics(Request $request, string $type, int $id)
    {
        $this->getDatabaseType($type);
        $questionnaire = Questionnaire::with(['sections.questions'])->findOrFail($id);
        $dosenId = $request->input('dosen_id');

        $responsesQuery = $questionnaire->responses();
        if ($dosenId && $questionnaire->type === 'kinerja_pengajar') {
            $responsesQuery->where('dosen_id', $dosenId);
        }
        $totalRespondents = $responsesQuery->count();

        // Hitung total target berdasarkan target_respondent dan dosen terpilih
        $target = $questionnaire->target_respondent;
        $totalTarget = 0;

        if ($dosenId && $questionnaire->type === 'kinerja_pengajar') {
            // Target adalah seluruh mahasiswa yang kelasnya diajar oleh dosen terpilih
            $totalTarget = Mahasiswa::whereIn('kelas_id', Jadwal::where('dosens_id', $dosenId)->pluck('kelas_id'))->count();
        } else {
            if ($target === 'all') {
                $totalTarget = Mahasiswa::count() + Dosen::count() + Pegawai::count();
            } elseif ($target === 'mahasiswa') {
                $totalTarget = Mahasiswa::count();
            } elseif ($target === 'dosen') {
                $totalTarget = Dosen::count();
            } elseif ($target === 'pegawai') {
                $totalTarget = Pegawai::count();
            } elseif ($target === 'dosen_pegawai') {
                $totalTarget = Dosen::count() + Pegawai::count();
            }
        }

        // Antisipasi jika data response melebihi target di db local
        if ($totalTarget < $totalRespondents) {
            $totalTarget = $totalRespondents;
        }

        $pendingRespondents = max(0, $totalTarget - $totalRespondents);
        $filledPercentage = $totalTarget > 0 ? round(($totalRespondents / $totalTarget) * 100, 1) : 0;
        $pendingPercentage = $totalTarget > 0 ? round(($pendingRespondents / $totalTarget) * 100, 1) : 0;

        $targetStats = [
            'total' => $totalTarget,
            'filled' => $totalRespondents,
            'pending' => $pendingRespondents,
            'filled_percentage' => $filledPercentage,
            'pending_percentage' => $pendingPercentage,
        ];

        // Ambil daftar dosen yang dinilai di kuisioner ini (untuk dropdown filter admin)
        $evaluatedDosens = [];
        if ($questionnaire->type === 'kinerja_pengajar') {
            $dosenIds = DB::table('questionnaire_responses')
                ->where('questionnaire_id', $id)
                ->whereNotNull('dosen_id')
                ->distinct()
                ->pluck('dosen_id');

            $evaluatedDosens = Dosen::whereIn('id', $dosenIds)
                ->orderBy('nama')
                ->get(['id', 'nama']);
        }

        $analytics = [];

        foreach ($questionnaire->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'questions' => [],
            ];

            foreach ($section->questions as $question) {
                $questionData = [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'is_required' => $question->is_required,
                    'options' => $question->options ?? [],
                    'total_answers' => 0,
                    'data' => [],
                ];

                // Ambil semua jawaban untuk pertanyaan ini (filter dosen jika dipilih)
                $answersQuery = DB::table('questionnaire_answers')
                    ->join('questionnaire_responses', 'questionnaire_answers.response_id', '=', 'questionnaire_responses.id')
                    ->where('questionnaire_responses.questionnaire_id', $questionnaire->id)
                    ->where('questionnaire_answers.question_id', $question->id);

                if ($dosenId && $questionnaire->type === 'kinerja_pengajar') {
                    $answersQuery->where('questionnaire_responses.dosen_id', $dosenId);
                }

                $answers = $answersQuery->pluck('questionnaire_answers.answer_value');
                $questionData['total_answers'] = $answers->count();

                if (in_array($question->question_type, ['radio', 'select', 'checkbox'])) {
                    // Pilihan ganda
                    $counts = [];
                    foreach ($question->options as $opt) {
                        $counts[$opt] = 0;
                    }

                    foreach ($answers as $ans) {
                        if ($question->question_type === 'checkbox') {
                            $decoded = json_decode($ans, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $val) {
                                    if (isset($counts[$val])) {
                                        $counts[$val]++;
                                    }
                                }
                            }
                        } else {
                            if (isset($counts[$ans])) {
                                $counts[$ans]++;
                            }
                        }
                    }

                    // Format chart
                    foreach ($counts as $label => $count) {
                        $percentage = $totalRespondents > 0 ? round(($count / $totalRespondents) * 100, 1) : 0;
                        $questionData['data'][] = [
                            'label' => $label,
                            'count' => $count,
                            'percentage' => $percentage,
                        ];
                    }
                } else {
                    // Paragraph / teks bebas
                    $questionData['data'] = $answers->take(50)->toArray();
                }

                $sectionData['questions'][] = $questionData;
            }

            $analytics[] = $sectionData;
        }

        return Inertia::render('Admin/Questionnaire/Analytics', [
            'questionnaire' => $questionnaire,
            'totalRespondents' => $totalRespondents,
            'targetStats' => $targetStats,
            'analytics' => $analytics,
            'evaluatedDosens' => $evaluatedDosens,
            'filters' => [
                'dosen_id' => $dosenId ? (int) $dosenId : null,
            ],
            'category' => $type,
            'categoryName' => match ($type) {
                'pelayanan' => 'Kuis Pelayanan',
                'kinerja-pengajar' => 'Kinerja Pengajar',
                'ami' => 'Kuisioner AMI',
            },
        ]);
    }

    /**
     * Export questionnaire answers as Excel or CSV.
     */
    public function export(string $type, int $id, string $format)
    {
        $this->getDatabaseType($type);
        $questionnaire = Questionnaire::findOrFail($id);

        $fileFormat = strtolower($format) === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $extension = strtolower($format) === 'csv' ? 'csv' : 'xlsx';

        $fileName = 'rekap-kuisioner-'.str_replace(' ', '-', strtolower($questionnaire->title)).'-'.date('YmdHis').'.'.$extension;

        return Excel::download(new QuestionnaireResponseExport($questionnaire), $fileName, $fileFormat);
    }
}
