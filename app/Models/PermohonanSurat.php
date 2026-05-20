<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanSurat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'permohonan_surats';

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
    protected $casts = [
        'data_diminta' => 'array',
        'anggota_tim' => 'array',
    ];

    public function getPangkatAtauGolonganAttribute()
    {
        return $this->attributes['pangkat_golongan'] ?? '-';
    }
}
