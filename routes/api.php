<?php

use App\Enums\ApplicationEnum;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [\App\Http\Controllers\AuthController::class, 'user']);
});

Route::middleware(EnsureClientIsResourceOwner::class)->group(function () {
    Route::post('accounts', function (Request $request) {
        $application = ApplicationEnum::from(auth('api')->client()->name);

        $validData = $request->validate([
            'accounts' => 'array',
            'accounts.*.uuid' => 'nullable|uuid',
            'accounts.*.name' => 'required|string',
            'accounts.*.short_name' => 'nullable|string',
            'accounts.*.deleted_at' => 'nullable|date',
        ]);

        $mappedAccounts = Model::withoutEvents(fn () => collect($validData['accounts'])
            ->map(function ($accountData) use ($application) {
                if (! $accountData['uuid']) {
                    unset($accountData['uuid']);
                }

                $account = Account::query()
                    ->withTrashed()
                    ->updateOrCreate(
                        match (true) {
                            isset($accountData['uuid']) => Arr::only($accountData, 'uuid'),
                            default => Arr::only($accountData, 'name'),
                        },
                        $accountData
                    );

                $account->applications = ($account->applications ?: collect())
                    ->push($application)
                    ->unique()
                    ->values();

                $account->save();

                return $account->only(['uuid', 'name']);
            }));

        return [
            'accounts' => $mappedAccounts,
        ];
    });

    Route::post('users', function (Request $request) {
        $validData = $request->validate([
            'users' => 'array',
            'users.*.uuid' => 'nullable|uuid',
            'users.*.name' => 'required|string',
            'users.*.email' => 'nullable|email',
            'users.*.mobile' => 'nullable|string',
            'users.*.is_internal' => 'required|boolean',
            'users.*.email_verified_at' => 'nullable|date',
            'users.*.deleted_at' => 'nullable|date',
            'users.*.password' => 'nullable',
            'users.*.roles' => 'array',
            'users.*.roles.*' => 'uuid',
            'users.*.accounts' => 'array',
            'users.*.accounts.*' => 'uuid',
        ]);

        $users = Model::withoutEvents(fn () => collect($validData['users'])->map(function ($userData) {
            if (! $userData['uuid']) {
                unset($userData['uuid']);
            }

            $userDataToUpdate = Arr::only($userData, [
                'uuid',
                'name',
                'email',
                'mobile',
                'is_internal',
                'email_verified_at',
                'password',
                'deleted_at',
            ]);

            $user = User::query()
                ->withTrashed()
                ->firstOrCreate(
                    match (true) {
                        isset($userData['uuid']) => Arr::only($userData, ['uuid']),
                        isset($userData['email']) => Arr::only($userData, ['email']),
                        default => Arr::only($userData, ['name', 'mobile']),
                    }, $userDataToUpdate);

            foreach ($userDataToUpdate as $key => $value) {
                $user->{$key} = $value;
            }
            $user->save();

            $user->syncRoles(Role::whereIn('uuid', Arr::wrap($userData['roles']))->get());

            $user->accounts()->sync(Account::whereIn('uuid', $userData['accounts'] ?? [])->pluck('id'));

            return $user->only(['uuid', 'email', 'mobile', 'name']);
        }));

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'users' => $users,
        ];
    });

    Route::post('/roles', action: function (Request $request) {
        $application = ApplicationEnum::from(auth('api')->client()->name);

        $validData = $request->validate([
            'roles' => 'array',
            'roles.*.uuid' => 'required|uuid',
            'roles.*.name' => 'required|string',
            'roles.*.guard_name' => 'required|string',
            'roles.*.is_internal' => 'required|boolean',
            'roles.*.permissions.*.uuid' => 'required|uuid',
            'roles.*.permissions.*.name' => 'required|string',
            'roles.*.permissions.*.guard_name' => 'required|string',
        ]);

        $allPermissions = collect($validData['roles'])
            ->pluck('permissions')
            ->flatten(1)
            ->unique()
            ->values();

        // Create permissions that don't exist
        Permission::upsert(
            $allPermissions->map(fn ($item) => [
                'app' => $application,
                'uuid' => $item['uuid'],
                'name' => $item['name'],
                'guard_name' => $item['guard_name'],
            ])->toArray(),
            ['uuid'],
        );

        $rolesDeleted = Role::where('app', $application)
            ->whereNotIn('uuid', collect($request->roles)->pluck('uuid'))
            ->delete();

        foreach ($request->roles as $roleData) {
            /** @var \App\Models\Role $role */
            $role = Role::updateOrCreate([
                'app' => $application,
                'uuid' => $roleData['uuid'],
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
