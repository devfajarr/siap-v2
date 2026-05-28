<?php

namespace App\Http\Controllers\V2\Respondent;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Respondent\Questionnaire\SubmitResponseRequest;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pegawai;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class QuestionnaireResponseController extends Controller
{
    /**
     * Helper to verify if the authenticated user matches the questionnaire target.
     */
    protected function authorizeRespondent(Questionnaire $questionnaire): void
    {
        // 1. Admin dan BPMI (creator / manager kuis) selalu bisa melihat & preview kuis
        $isAdmin = auth()->guard('admin')->check();
        $isBpmi = auth()->guard('jabatan')->check() && auth()->guard('jabatan')->user()->nama_jabatan === 'bpmi';

        if ($isAdmin || $isBpmi) {
            return;
        }

        // 2. Tentukan peran responden berdasarkan guard
        $role = '';
        if (auth()->guard('mahasiswa')->check()) {
            $role = 'mahasiswa';
        } elseif (auth()->guard('dosen')->check()) {
            $role = 'dosen';
        } elseif (auth()->guard('pegawai')->check()) {
            $role = 'pegawai';
        } elseif (auth()->guard('wakil_direktur')->check() || auth()->guard('direktur')->check() || auth()->guard('kaprodi')->check() || auth()->guard('jabatan')->check()) {
            // Pegawai struktural lainnya
            $role = 'pegawai';
        }

        $target = $questionnaire->target_respondent;

        if ($target === 'all') {
            return;
        }

        if ($target === 'mahasiswa' && $role !== 'mahasiswa') {
            abort(403, 'Kuisioner ini khusus untuk Mahasiswa.');
        }

        if ($target === 'dosen' && $role !== 'dosen') {
            abort(403, 'Kuisioner ini khusus untuk Dosen.');
        }

        if ($target === 'pegawai' && $role !== 'pegawai') {
            abort(403, 'Kuisioner ini khusus untuk Pegawai/Struktural.');
        }

        if ($target === 'dosen_pegawai' && ! in_array($role, ['dosen', 'pegawai'])) {
            abort(403, 'Kuisioner ini khusus untuk Dosen dan Pegawai.');
        }
    }

    /**
     * Display the questionnaire form.
     */
    public function show(int $id)
    {
        $questionnaire = Questionnaire::with(['sections' => function ($query) {
            $query->orderBy('order');
        }, 'sections.questions' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        if ($questionnaire->status !== 'published') {
            abort(404, 'Kuisioner tidak aktif atau sudah ditutup.');
        }

        $this->authorizeRespondent($questionnaire);

        $isAdmin = auth()->guard('admin')->check();
        $isBpmi = auth()->guard('jabatan')->check() && auth()->guard('jabatan')->user()->nama_jabatan === 'bpmi';
        $isPreview = $isAdmin || $isBpmi;

        return Inertia::render('Respondent/Questionnaire/Show', [
            'questionnaire' => $questionnaire,
            'isPreview' => $isPreview,
        ]);
    }

    /**
     * Submit respondent answers.
     */
    public function submit(SubmitResponseRequest $request, int $id)
    {
        $questionnaire = Questionnaire::with('questions')->findOrFail($id);

        if ($questionnaire->status !== 'published') {
            abort(404, 'Kuisioner sudah tidak menerima tanggapan.');
        }

        $this->authorizeRespondent($questionnaire);

        $validated = $request->validated();
        $answersData = $validated['answers'] ?? [];

        // Validasi pertanyaan wajib (is_required) secara dinamis
        $errors = [];
        foreach ($questionnaire->questions as $question) {
            $answer = $answersData[$question->id] ?? null;

            if ($question->is_required) {
                if (is_null($answer) || $answer === '' || (is_array($answer) && count($answer) === 0)) {
                    $errors["answers.{$question->id}"] = 'Pertanyaan ini wajib diisi.';
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($questionnaire, $answersData, $request) {
            // Simpan Sesi Response
            $response = QuestionnaireResponse::create([
                'questionnaire_id' => $questionnaire->id,
                'respondent_id' => auth()->id(),
                'respondent_type' => get_class(auth()->user()),
                'submitted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Simpan Detail Jawaban
            foreach ($questionnaire->questions as $question) {
                $answerVal = $answersData[$question->id] ?? null;

                if (! is_null($answerVal)) {
                    // Jika bertipe array (misal checkbox), simpan sebagai JSON string
                    $formattedVal = is_array($answerVal) ? json_encode($answerVal) : (string) $answerVal;

                    $response->answers()->create([
                        'question_id' => $question->id,
                        'answer_value' => $formattedVal,
                    ]);
                }
            }
        });

        // Redirect ke halaman daftar kuisioner atau dashboard V2 yang sesuai
        if (auth()->guard('mahasiswa')->check()) {
            return redirect()->route('v2.mahasiswa.kuisioner.index')
                ->with('success', 'Tanggapan kuisioner Anda berhasil dikirim. Terima kasih atas partisipasi Anda.');
        }

        if (auth()->guard('dosen')->check()) {
            return redirect()->route('v2.dosen.kuisioner.index')
                ->with('success', 'Tanggapan kuisioner Anda berhasil dikirim. Terima kasih atas partisipasi Anda.');
        }

        if (auth()->guard('pegawai')->check()) {
            return redirect()->route('v2.pegawai.kuisioner.index')
                ->with('success', 'Tanggapan kuisioner Anda berhasil dikirim. Terima kasih atas partisipasi Anda.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Tanggapan kuisioner Anda berhasil dikirim. Terima kasih atas partisipasi Anda.');
    }

    /**
     * Display a listing of available questionnaires for Mahasiswa.
     */
    public function index()
    {
        if (! auth()->guard('mahasiswa')->check()) {
            abort(403, 'Akses khusus Mahasiswa.');
        }

        $mahasiswaId = auth()->id();

        // Ambil kuisioner tipe pelayanan dan kinerja_pengajar yang dipublish
        // dan menargetkan mahasiswa / publik.
        $questionnaires = Questionnaire::query()
            ->whereIn('type', ['pelayanan', 'kinerja_pengajar'])
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'mahasiswa'])
            ->withCount(['responses' => function ($query) use ($mahasiswaId) {
                $query->where('respondent_id', $mahasiswaId)
                    ->where('respondent_type', Mahasiswa::class);
            }])
            ->latest()
            ->get();

        return Inertia::render('Respondent/Questionnaire/Index', [
            'questionnaires' => $questionnaires,
        ]);
    }

    /**
     * Display a listing of available questionnaires for Dosen.
     */
    public function dosenIndex()
    {
        /** @var Dosen $dosen */
        $dosen = Auth::guard('dosen')->user();
        $dosenId = $dosen->id;

        // Ambil kuisioner tipe ami & pelayanan yang dipublish dan menargetkan dosen.
        $questionnaires = Questionnaire::query()
            ->whereIn('type', ['ami', 'pelayanan'])
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'dosen', 'dosen_pegawai'])
            ->withCount(['responses' => function ($query) use ($dosenId) {
                $query->where('respondent_id', $dosenId)
                    ->where('respondent_type', Dosen::class);
            }])
            ->latest()
            ->get();

        return Inertia::render('Respondent/Questionnaire/Index', [
            'questionnaires' => $questionnaires,
        ]);
    }

    /**
     * Display a listing of available questionnaires for Pegawai.
     */
    public function pegawaiIndex()
    {
        /** @var Pegawai $pegawai */
        $pegawai = Auth::guard('pegawai')->user();
        $pegawaiId = $pegawai->id;

        // Ambil kuisioner tipe ami & pelayanan yang dipublish dan menargetkan pegawai.
        $questionnaires = Questionnaire::query()
            ->whereIn('type', ['ami', 'pelayanan'])
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'pegawai', 'dosen_pegawai'])
            ->withCount(['responses' => function ($query) use ($pegawaiId) {
                $query->where('respondent_id', $pegawaiId)
                    ->where('respondent_type', Pegawai::class);
            }])
            ->latest()
            ->get();

        return Inertia::render('Respondent/Questionnaire/Index', [
            'questionnaires' => $questionnaires,
        ]);
    }
}
