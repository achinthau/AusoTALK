<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

class Cdr extends Model
{
    protected $connection = 'mysql-voice';

    protected $table = 'cdr';

    protected $fillable = [
        'uniqueid',
        'calldate',
        'src',
        'dst',
        'dcontext',
        'channel',
        'dstchannel',
        'lastapp',
        'lastdata',
        'duration',
        'billsec',
        'disposition',
        'amaflags',
        'accountcode',
        'userfield',
        'peeraccount',
    ];

    protected $casts = [
        'calldate' => 'datetime',
        'duration' => 'integer',
        'billsec' => 'integer',
    ];

    public $timestamps = false;

    /**
     * Get the extension from dstchannel (e.g. PJSIP/3000-00000006 -> 3000)
     * Note: Prefix with underscore to avoid automatic accessor invocation in table queries
     */
    public function getExtensionAttribute(): ?string
    {
        if ($this->dstchannel && preg_match('/\/(\d+)-/', $this->dstchannel, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the call direction based on source digit length
     * Note: Prefix with underscore to avoid automatic accessor invocation in table queries
     */
    public function getDirectionAttribute(): string
    {
        return strlen($this->src) === 9 ? 'Out' : 'In';
    }

    /**
     * Convert seconds to human readable format
     */
    public function getBillSecDurationAttribute(): string
    {
        $seconds = $this->billsec;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Query scope to compute expensive attributes efficiently
     * Usage: Cdr::withComputedAttributes()->get()
     */
    public function scopeWithComputedAttributes($query)
    {
        return $query->addSelect([
            'computed_extension' => Expression::raw(
                "REGEXP_SUBSTR(dstchannel, '/([0-9]+)-', 1, 1, 'i', 1)"
            ),
            'computed_direction' => Expression::raw(
                "CASE WHEN CHAR_LENGTH(src) = 9 THEN 'Out' ELSE 'In' END"
            ),
        ]);
    }
}
