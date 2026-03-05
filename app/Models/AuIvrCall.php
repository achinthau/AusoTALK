<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuIvrCall extends Model
{
    protected $connection = 'mysql-voice';

    protected $table = 'au_ivr_calls';

    protected $fillable = [
        'uniqueid',
        'date',
        'ani',
        'dnis',
        'ivr',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public $timestamps = false;

    public function cdr()
    {
        return $this->hasOne(Cdr::class, 'uniqueid', 'uniqueid');
    }

    public function getBillSecDurationAttribute(): string
    {
        $billsec = $this->cdr?->billsec ?? 0;

        return $this->formatDuration((int) $billsec);
    }

    private function formatDuration(int $seconds): string
    {
        $hours = (int) ($seconds / 3600);
        $minutes = (int) (($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
