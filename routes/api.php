<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return [
            'id' => $user->uuid,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'is_internal' => $user->is_internal,
            'accounts' => [],
            'roles' => $user->roles->pluck('name'),
        ];
    });


});

Route::middleware(EnsureClientIsResourceOwner::class)->group(function () {
    Route::post('accounts', function (Request $request) {
        foreach ($request->accounts as $account) {
            Account::updateOrCreate(['uuid' => $account['uuid']], $account);
        }

        return [
            'success' => true,
        ];
    });

    Route::post('users', function (Request $request) {
        foreach ($request->users as $userData) {
            $user = User::query()
                        ->withTrashed()
                        ->updateOrCreate(['uuid' => $userData['uuid']], Arr::only($userData, [
                            'name',
                            'first_name',
                            'last_name',
                            'name',
                            'email',
                            'is_internal',
                            'email_verified_at',
                            'password',
                            'deleted_at',
                        ]));
            $user->syncRoles($userData['roles']);
            $user->accounts()->sync(Account::whereIn('uuid', $userData['accounts'])->pluck('id'));
        }

        return [
            'success' => true,
        ];
    });
});
