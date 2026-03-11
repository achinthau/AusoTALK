<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Callcount extends Model
{
    protected $connection = 'mysql-voice';

    protected $table = 'callcount';

    public $timestamps = false;

    protected $fillable = [
        'uniqueid',
        'hotline',
        'callcount',
        'direction',
        'status',
        'ani',
        'dnis',
        'date',
        'customer_reaction',
        'comment',
        'app',
        'tenant',
    ];
}
