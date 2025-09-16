<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\HasUuids;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes, HasUuids;

//    /**
//     * The attributes that are mass assignable.
//     *
//     * @var list<string>
//     */
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];

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
//            'password' => 'hashed',
            'is_internal' => 'boolean',
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
        return $this->is_internal;
    }

    public function getFilamentName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function canImpersonate(): bool
    {
        return true; // TODO
        return $this->hasPermissionTo('users.impersonate');
    }
}
