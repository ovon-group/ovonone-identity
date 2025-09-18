<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\HasUuids;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements FilamentUser, HasName, HasPasskeys
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, InteractsWithPasskeys, Notifiable, SoftDeletes;

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
            'is_internal' => 'boolean',
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;

        return $this->is_internal;
    }

    public function canImpersonate(): bool
    {
        return true; // TODO

        return $this->hasPermissionTo('users.impersonate');
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function applicationPayload($application)
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'is_internal' => $this->is_internal,
            'accounts' => $this->accounts()->whereJsonContains('applications', $application)->pluck('uuid'),
            'roles' => $this->roles()->where('app', $application)->pluck('name'),
        ];
    }

    public function getApplications(): array
    {
        return $this->accounts()
            ->pluck('applications')
            ->flatten()
            ->unique()
            ->all();
    }
}
