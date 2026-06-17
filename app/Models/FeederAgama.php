<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeederAgama extends Model
{
    use HasFactory;

    protected $table = 'feeder_agamas';

    protected $primaryKey = 'id_agama';

    public $incrementing = false;

    protected $guarded = [];
}
