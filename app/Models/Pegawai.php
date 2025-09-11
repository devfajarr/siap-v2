<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pegawai extends Authenticatable
{
    use HasFactory,SoftDeletes,Notifiable;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];
}
