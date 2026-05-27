<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use HasFactory,Notifiable,SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'pegawais_id', 'id');
    }
}
