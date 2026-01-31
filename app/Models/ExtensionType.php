<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExtensionType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_extension_type');
    }
}
