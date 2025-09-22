<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        if (!$user instanceof User) {
            return false;
        }

        // This override prevents applications from being authorised if
        // the user does not belong to an account with access to it

        // Get the application environment for this client
        $appEnvironment = $this->owner;
        if (!$appEnvironment) {
            return false;
        }

        // Check if user can access the application
        return $user->canAccessApplication($appEnvironment->application);
    }

    public function applicationEnvironment(): BelongsTo
    {
        return $this->belongsTo(ApplicationEnvironment::class, 'id', 'client_id');
    }
}
