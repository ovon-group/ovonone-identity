<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $application = $user->token()->client->name;

        return $user->applicationPayload($application);
    }
}
