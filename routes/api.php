<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Spatie\Permission\Models\Permission;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [\App\Http\Controllers\AuthController::class, 'user']);
});

Route::middleware(EnsureClientIsResourceOwner::class)->group(function () {
    Route::post('accounts', function (Request $request) {
        $mappedAccounts = collect($request->accounts)
            ->map(function ($accountData) {
                $account = Account::updateOrCreate(
                    ['name' => $accountData['name']],
                    [
                        'short_name' => $accountData['short_name'] ?? \Illuminate\Support\Str::shortName($accountData['name']),
                    ],
                );
                return $account->only(['uuid', 'name']);
            });

        return [
            'accounts' => $mappedAccounts,
        ];
    });

    Route::post('users', function (Request $request) {
        $users = collect($request->users)->map(function ($userData) {
            $user = User::withoutEvents(function () use ($userData) {
                return User::query()
                        ->withTrashed()
                        ->updateOrCreate(
                            $userData['email']
                                ? ['email' => $userData['email']]
                                : array_filter(Arr::only($userData, [
                                    'name',
                                    'mobile',
                                ])),
                            Arr::only($userData, [
                                'name',
                                'email',
                                'mobile',
                                'is_internal',
                                'email_verified_at',
                                'password',
                                'deleted_at',
                            ]));
            });

            $user->syncRoles($userData['roles'] ?? []);
            $user->accounts()->sync(Account::whereIn('uuid', $userData['accounts'] ?? [])->pluck('id'));

            return $user->only(['uuid', 'email', 'mobile', 'name']);
        });

        return [
            'users' => $users,
        ];
    });

    Route::post('{appName}/roles', function (Request $request, string $appName) {
        $allPermissions = collect($request->roles)
            ->pluck('permissions')
            ->flatten(1)
            ->unique()
            ->values();

        // Create permissions that don't exist
        Permission::upsert(
            $allPermissions->map(fn($item) => [
                'app' => $appName,
                'name' => $item['name'],
                'guard_name' => $item['guard_name'],
            ])
                           ->toArray(),
            ['app', 'name', 'guard_name'],
        );

        // Delete removed permissions

        $allPermissions->keyBy('guard_name')
                       ->each(function ($group, $guardName) use ($appName) {
                           Permission::where('app', $appName)
                                     ->where('guard_name', $guardName)
                                     ->whereNotIn('name', collect($group)->pluck('name'))
                                     ->delete();
                       });

        foreach ($request->roles as $roleData) {
            $role = \Spatie\Permission\Models\Role::updateOrCreate([
                'app' => $appName,
                'name' => $roleData['name'],
            ], [
                'guard_name' => $roleData['guard_name'],
                'is_internal' => $roleData['is_internal'],
            ]);

            $role->syncPermissions(collect($roleData['permissions'])->pluck('name'));
        }

        $deleted = Permission::doesntHave('roles')->delete();

        return [
            'success' => true,
            'deleted' => $deleted,
        ];
    });

});
