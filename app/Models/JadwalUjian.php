<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalUjian extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function pengawas(): MorphTo
    {
        return $this->morphTo('pengawas');
    }

    public function matkul(): BelongsTo
    {
        return $this->belongsTo(Matkul::class, 'matkuls_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangans_id');
    }
}
