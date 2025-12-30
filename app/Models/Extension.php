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
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function extensionType(): BelongsTo
    {
        return $this->belongsTo(ExtensionType::class);
    }
}
