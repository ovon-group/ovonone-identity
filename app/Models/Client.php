<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    protected $casts = [
        'grant_types' => 'array',
        'redirect_uris' => 'array',
    ];

    /**
     * Get the grant types for the client.
     */
    protected function grantTypes(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): array {
                if ($value) {
                    $decoded = json_decode($value, true);
                    return is_array($decoded) ? $decoded : ['personal_access'];
                }
                return ['personal_access'];
            }
        );
    }

    protected function redirectUris(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes): array {
                if (!empty($value)) {
                    $decoded = json_decode($value, true);
                    return is_array($decoded) ? $decoded : [];
                }
                if (!empty($attributes['redirect'])) {
                    return explode(',', $attributes['redirect']);
                }
                return [];
            }
        );
    }

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        // This override prevents applications from being authorised if
        // the user does not belong to an account with access to it

        return $user->canAccessApplication(ApplicationEnum::from($this->name));
    }
}
