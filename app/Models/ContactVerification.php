<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactVerification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Dapatkan model verifiable (Mahasiswa, Dosen, dsb.)
     */
    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
