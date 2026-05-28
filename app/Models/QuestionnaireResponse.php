<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the questionnaire associated with the response.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Get the answers for the response.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'response_id');
    }

    /**
     * Get the respondent (Mahasiswa or Dosen or Pegawai).
     */
    public function respondent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the evaluated lecturer (dosen) associated with the response.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Get the evaluated subject (matkul) associated with the response.
     */
    public function matkul(): BelongsTo
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }

    /**
     * Get the evaluated schedule (jadwal) associated with the response.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}
