<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Wadir extends Authenticatable
{
    use HasFactory,Notifiable;

    protected $guarded = ['id'];

    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function receivMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosens_id', 'id');
    }
}
