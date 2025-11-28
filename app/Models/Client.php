<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        // This override prevents applications from being authorised if
        // the user does not belong to an account with access to it

        return $user->canAccessApplication(ApplicationEnum::from($this->name));
    }
}
