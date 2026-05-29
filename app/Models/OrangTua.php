<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrangTua extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'orang_tuas';

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
    ];

    /**
     * Relasi ke Mahasiswa (Many to Many).
     */
    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'mahasiswa_parent', 'orang_tua_id', 'mahasiswa_id')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }
}
