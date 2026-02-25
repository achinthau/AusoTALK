<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Extension extends Model
{
    protected $fillable = [
        'number',
        'company_id',
        'extension_type_id',
        'password',
        'context',
        'status',
        'exten_type',
        'updatedby',
        'api_status',
        'api_payload',
        'api_response',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'api_payload' => 'array',
            'api_response' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function extensionType(): BelongsTo
    {
        return $this->belongsTo(ExtensionType::class);
    }
}
