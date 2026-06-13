<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Dosen extends Authenticatable
{
    use HasFactory,Notifiable,SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function matkul()
    {
        return $this->hasMany(Matkul::class);
    }

    /**
     * Get the placeholder lecturer assigned to this lecturer for Neo Feeder reporting.
     */
    public function placeholderDosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'feeder_dosen_placeholder_id');
    }

    /**
     * Get the unregistered/honorer lecturers that use this lecturer as a placeholder.
     */
    public function placeholderForDosen(): HasMany
    {
        return $this->hasMany(Dosen::class, 'feeder_dosen_placeholder_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'dosens_id');
    }

    public function absen()
    {
        return $this->hasMany(Absen::class);
    }

    public function resume()
    {
        return $this->hasMany(Resume::class);
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function pengajuanBerita()
    {
        return $this->hasMany(PengajuanRekapBerita::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function receivMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }

    public function direktur()
    {
        return $this->hasOne(Direktur::class);
    }

    public function wadir()
    {
        return $this->hasOne(Wadir::class);
    }

    public function kaprodi()
    {
        return $this->hasMany(Kaprodi::class);
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'dosens_id', 'id');
    }
}
