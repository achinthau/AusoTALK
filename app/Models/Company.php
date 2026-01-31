<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'email',
        'hotline',
        'primary_email_enabled',
        'context',
        'concurrent_channels',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }

    public function extensionTypes(): BelongsToMany
    {
        return $this->belongsToMany(ExtensionType::class, 'company_extension_type');
    }
}
