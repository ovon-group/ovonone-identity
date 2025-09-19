<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\ApplicationEnum;
use App\Models\Traits\HasUuids;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements FilamentUser, HasName, HasPasskeys
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasOneTimePasswords, HasRoles, HasUuids, InteractsWithPasskeys, Notifiable, SoftDeletes;

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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'is_internal' => $this->is_internal,
            'accounts' => $this->accounts()->whereJsonContains('applications', $application)->pluck('uuid'),
            'roles' => $this->roles()->where('app', $application)->pluck('name'),
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function getApplications(): array
    {
        if ($this->is_internal) {
            return ApplicationEnum::cases();
        }

        return $this->accounts()
            ->pluck('applications')
            ->flatten()
            ->unique()
            ->all();
    }

    public function canAccessApplication(ApplicationEnum $application): bool
    {
        if ($this->is_internal) {
            return true;
        }

        return $this->accounts()
            ->whereJsonContains('applications', $application)
            ->exists();
    }

    /**
     * Send OTP via email
     */
    public function sendOneTimePasswordViaEmail(): self
    {
        $oneTimePassword = $this->createOneTimePassword();
        $this->notify(new \App\Notifications\OneTimePasswordNotification($oneTimePassword, 'mail'));
        
        return $this;
    }

    /**
     * Send OTP via SMS
     */
    public function sendOneTimePasswordViaSms(): self
    {
        if (!$this->mobile) {
            throw new \Exception('User does not have a mobile number');
        }
        
        $oneTimePassword = $this->createOneTimePassword();
        $this->notify(new \App\Notifications\OneTimePasswordNotification($oneTimePassword, 'sms'));
        
        return $this;
    }

    /**
     * Get the phone number for SMS notifications
     */
    public function routeNotificationForTwilio(): ?string
    {
        return str_starts_with($this->mobile, '0') ? '+44'.ltrim($this->mobile, '0') : (str_starts_with($this->mobile, '44') ? '+'.$this->mobile : $this->mobile);
    }

    /**
     * Check if the user has a password set
     */
    public function hasPassword(): bool
    {
        return !is_null($this->password) && !empty($this->password);
    }
}
