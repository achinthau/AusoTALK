<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtensionType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }
}
