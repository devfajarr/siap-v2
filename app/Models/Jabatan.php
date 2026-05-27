<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Jabatan extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $table = 'jabatans';

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosens_id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawais_id', 'id');
    }
}
