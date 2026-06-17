<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederWilayah extends Model
{
    use HasFactory;

    protected $table = 'feeder_wilayahs';

    protected $primaryKey = 'id_wilayah';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    /**
     * Get the parent region (induk wilayah).
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'id_induk_wilayah', 'id_wilayah');
    }
}
