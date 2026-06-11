<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasRole(string $roleKey): bool
    {
        return $this->roles()->where('key', $roleKey)->exists();
    }

    public function canDo(string $permissionKey): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        return $this->roles()->whereHas('permissions', function ($q) use ($permissionKey) {
            $q->where('key', $permissionKey);
        })->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
