<?php

use App\Enums\ApplicationEnum;
use App\Models\Account;
use App\Models\ApplicationEnvironment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [\App\Http\Controllers\AuthController::class, 'user']);
});

Route::middleware(EnsureClientIsResourceOwner::class)->group(function () {
    Route::post('accounts', function (Request $request) {
        $validData = $request->validate([
            'accounts' => 'array',
            'accounts.*.name' => 'required|string',
            'accounts.*.short_name' => 'nullable|string',
            'accounts.*.deleted_at' => 'nullable|date',
        ]);

        /** @var ApplicationEnvironment $applicationEnvironment */
        $applicationEnvironment = auth('api')->client()->owner;
        $application = $applicationEnvironment->application;

        $mappedAccounts = Model::withoutEvents(fn() => collect($validData['accounts'])
            ->map(function ($accountData) use ($validData, $application) {
                $account = Account::query()
                    ->withTrashed()
                    ->updateOrCreate(
                        ['name' => $accountData['name']],
                        $accountData
                    );

                $account->applications()->syncWithoutDetaching($application->getKey());

                return $account->only(['uuid', 'name']);
            }));

        return [
            'accounts' => $mappedAccounts,
        ];
    });

    Route::post('users', function (Request $request) {
        $request->validate([
            'users' => 'array',
            'users.*.name' => 'required|string',
            'users.*.email' => 'nullable|email',
            'users.*.mobile' => 'nullable|string',
            'users.*.is_internal' => 'required|boolean',
            'users.*.email_verified_at' => 'nullable|date',
            'users.*.deleted_at' => 'nullable|date',
            'users.*.password' => 'nullable',
        ]);

        $users = Model::withoutEvents(fn () => collect($request->users)->map(function ($userData) {
            $user = User::query()
                ->withTrashed()
                ->updateOrCreate(
                    isset($userData['email']) && $userData['email']
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

            $user->syncRoles($userData['roles'] ?? []);
            $user->accounts()->sync(Account::whereIn('uuid', $userData['accounts'] ?? [])->pluck('id'));

            return $user->only(['uuid', 'email', 'mobile', 'name']);
        }));

        return [
            'users' => $users,
        ];
    });

    Route::post('/roles', function (Request $request) {
        $validData = $request->validate([
            'roles' => 'array',
            'roles.*.name' => 'required|string',
            'roles.*.guard_name' => 'required|string',
            'roles.*.is_internal' => 'required|boolean',
            'roles.*.permissions.*.name' => 'required|string',
            'roles.*.permissions.*.guard_name' => 'required|string',
        ]);

        $allPermissions = collect($validData['roles'])
            ->pluck('permissions')
            ->flatten(1)
            ->unique()
            ->values();

        /** @var ApplicationEnvironment $applicationEnvironment */
        $applicationEnvironment = auth('api')->client()->owner;

        // Create permissions that don't exist
        Permission::upsert(
            $allPermissions->map(fn ($item) => [
                'application_environment_id' => $applicationEnvironment->getKey(),
                'name' => $item['name'],
                'guard_name' => $item['guard_name'],
            ])->toArray(),
            ['app', 'name', 'guard_name'],
        );

        foreach ($request->roles as $roleData) {
            $role = Role::updateOrCreate([
                'application_environment_id' => $applicationEnvironment->getKey(),
                'name' => $roleData['name'],
            ], [
                'guard_name' => $roleData['guard_name'],
                'is_internal' => $roleData['is_internal'],
            ]);
            $role->syncPermissions(collect($roleData['permissions'])->pluck('name'));
        }

        $rolesDeleted = Role::where('application_environment_id', $applicationEnvironment->getKey())
            ->whereNotIn('name', collect($request->roles)->pluck('name'))
            ->delete();

        $deleted = Permission::doesntHave('roles')->delete();

        return [
            'success' => true,
            'deleted' => $deleted,
        ];
    });

});
