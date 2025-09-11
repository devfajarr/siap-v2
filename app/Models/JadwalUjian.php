<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalUjian extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function pegawai(){
        return $this->belongsTo(Pegawai::class,'pegawais_id');
    }
    public function matkul(){
        return $this->belongsTo(Matkul::class,'matkuls_id');
    }
    public function kelas(){
        return $this->belongsTo(Kelas::class,'kelas_id');
    }
    public function ruangan(){
        return $this->belongsTo(Ruangan::class,'ruangans_id');
    }
}
