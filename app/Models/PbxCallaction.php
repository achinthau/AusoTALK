<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PbxCallaction extends Model
{
    protected $connection = 'mysql-voice';

    protected $table = 'pbx_callaction';

    public $timestamps = false;

    protected $fillable = [
        'uniqueid',
        'tenant',
        'count',
        'queuename',
        'ani',
        'dnis',
        'dialstatus',
        'status',
        'date',
        'abandoned',
    ];
}
