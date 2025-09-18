<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
//    protected $casts = [
//        'name' => ApplicationEnum::class,
//    ];

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return $this->firstParty();
    }
}
