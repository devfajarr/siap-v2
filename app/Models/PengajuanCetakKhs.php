<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCetakKhs extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cetak_khs';

    protected $guarded = ['id'];

    /**
     * Relasi ke model Mahasiswa.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relasi ke model Semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    /**
     * Relasi ke model Admin (Petugas).
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'petugas_id');
    }
}
