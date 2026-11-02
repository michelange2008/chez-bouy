<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    protected $attributes = [
        'is_active' => true,
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function hasExpired(): bool
    {
        return $this->role === 'locataire'
            && $this->created_at !== null
            && $this->created_at->copy()->addYearsNoOverflow(2)->lessThanOrEqualTo(now());
    }

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
            'last_login_at' => 'datetime',
            'login_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
