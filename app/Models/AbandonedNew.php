<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedNew extends Model
{
    protected $connection = 'mysql-old';

    protected $table = 'au_abandoned_report';

    public $timestamps = false;

    protected $fillable = ['ani', 'dnis', 'queuename', 'recalled_status', 'received_time', 'recalled_time'];
}
