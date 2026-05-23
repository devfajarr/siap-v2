<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Kaprodi extends Authenticatable
{
    use HasFactory,Notifiable;

    protected $guarded = ['id'];

    protected $table = 'kaprodi';

    public function prodis()
    {
        return $this->belongsToMany(Prodi::class, 'kaprodi_prodi', 'kaprodi_id', 'prodi_id');
    }

    public function dosen(){
        return $this->belongsTo(Dosen::class,'dosens_id','id');
    }
}
