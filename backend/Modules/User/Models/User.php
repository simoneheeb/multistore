<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\User\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'is_active',
        'is_admin',
    ];

    /**
     * Never serialise credentials, even by accident.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    public static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
