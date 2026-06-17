<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Mahasiswa extends Authenticatable
{
    use HasFactory, Notifiable,SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Orang Tua (Many to Many).
     */
    public function orangTuas(): BelongsToMany
    {
        return $this->belongsToMany(OrangTua::class, 'mahasiswa_parent', 'mahasiswa_id', 'orang_tua_id')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function absen()
    {
        return $this->hasMany(Absen::class, 'absens_id');
    }

    public function aktif()
    {
        return $this->hasMany(Aktif::class);
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'absens_id');
    }

    public function uts()
    {
        return $this->hasMany(Uts::class);
    }

    public function uas()
    {
        return $this->hasMany(Uas::class);
    }

    public function pembimbingAkademik()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    public function nilaiHuruf()
    {
        return $this->hasMany(NilaiHuruf::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function krs()
    {
        return $this->hasMany(Krs::class);
    }

    /**
     * Check if the student has completed all teacher performance evaluations for the active semester.
     */
    public function hasCompletedAllTeacherEvaluations(): bool
    {
        $kelas = $this->kelas;
        if (! $kelas) {
            return true;
        }

        $questionnaires = Questionnaire::where('type', 'kinerja_pengajar')
            ->where('status', 'published')
            ->get();

        if ($questionnaires->isEmpty()) {
            return true;
        }

        $schedules = Jadwal::where('kelas_id', $this->kelas_id)
            ->whereHas('kelas.semester', function ($query) {
                $query->where('status', 1);
            })
            ->get();

        if ($schedules->isEmpty()) {
            return true;
        }

        foreach ($questionnaires as $q) {
            foreach ($schedules as $schedule) {
                $exists = QuestionnaireResponse::where('questionnaire_id', $q->id)
                    ->where('respondent_id', $this->id)
                    ->where('respondent_type', self::class)
                    ->where('dosen_id', $schedule->dosens_id)
                    ->where('jadwal_id', $schedule->id)
                    ->exists();

                if (! $exists) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get exam card request submissions for the student.
     */
    public function pengajuanKartuUjian(): HasMany
    {
        return $this->hasMany(PengajuanCetakKartuUjian::class, 'mahasiswa_id');
    }

    /**
     * Check if the student has completed all service questionnaires.
     */
    public function hasCompletedServiceEvaluations(): bool
    {
        $publishedPelayananCount = Questionnaire::where('type', 'pelayanan')
            ->where('status', 'published')
            ->whereIn('target_respondent', ['all', 'mahasiswa'])
            ->count();

        if ($publishedPelayananCount === 0) {
            return true;
        }

        $completedPelayananCount = QuestionnaireResponse::where('respondent_id', $this->id)
            ->where('respondent_type', self::class)
            ->whereHas('questionnaire', function ($q) {
                $q->where('type', 'pelayanan')
                    ->where('status', 'published');
            })
            ->count();

        return $completedPelayananCount >= $publishedPelayananCount;
    }

    /**
     * Relasi ke Feeder Wilayah.
     */
    public function feederWilayah()
    {
        return $this->belongsTo(FeederWilayah::class, 'id_wilayah', 'id_wilayah');
    }
}
