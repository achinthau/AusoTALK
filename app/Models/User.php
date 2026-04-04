<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'branch_id',
        'department_id',
        'phone',
        'extension',
        'nic',
        'gender',
        'address',
        'is_logged_in',
        'primary_extension',
        'secondary_extension',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_logged_in' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function extensionRecord(): BelongsTo
    {
        return $this->belongsTo(Extension::class, 'extension', 'number');
    }

    protected static function booted()
    {
        static::created(function (User $user) {
            if ($user->primary_extension) {
                Extension::where('number', $user->primary_extension)
                    ->where('company_id', $user->company_id)
                    ->update(['status' => 1]);
            }
            if ($user->secondary_extension) {
                Extension::where('number', $user->secondary_extension)
                    ->where('company_id', $user->company_id)
                    ->update(['status' => 1]);
            }
        });

        static::updating(function (User $user) {
            if ($user->isDirty('primary_extension')) {
                $oldExtension = $user->getOriginal('primary_extension');
                $newExtension = $user->primary_extension;

                if ($oldExtension) {
                    Extension::where('number', $oldExtension)
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 0]);
                }

                if ($newExtension) {
                    Extension::where('number', $newExtension)
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 1]);
                }
            }

            if ($user->isDirty('secondary_extension')) {
                $oldExtension = $user->getOriginal('secondary_extension');
                $newExtension = $user->secondary_extension;

                if ($oldExtension) {
                    Extension::where('number', $oldExtension)
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 0]);
                }

                if ($newExtension) {
                    Extension::where('number', $newExtension)
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 1]);
                }
            }
        });

        static::deleting(function (User $user) {
            if ($user->primary_extension) {
                Extension::where('number', $user->primary_extension)
                    ->where('company_id', $user->company_id)
                    ->update(['status' => 0]);
            }
            if ($user->secondary_extension) {
                Extension::where('number', $user->secondary_extension)
                    ->where('company_id', $user->company_id)
                    ->update(['status' => 0]);
            }
        });
    }
}
