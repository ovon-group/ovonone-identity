<?php

use App\Enums\ApplicationEnum;
use App\Jobs\SyncAccountWithApplications;
use App\Jobs\SyncUserWithApplications;
use App\Models\Account;
use App\Models\User;
use App\Services\ApplicationService\ApplicationApiService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    // Create OAuth client for API authentication
    $this->client = Client::factory()->create();

    Passport::actingAsClient($this->client);
});

test('it only syncs users to correct applications', function () {
    Bus::fake();
    Http::fake();

    // Create accounts with specific applications
    $protegoAccount = Account::factory()->create([
        'name' => 'Protego Account',
        'applications' => [ApplicationEnum::Protego],
    ]);

    $wheel2webAccount = Account::factory()->create([
        'name' => 'Wheel2Web Account',
        'applications' => [ApplicationEnum::Wheel2Web],
    ]);

    $bothAccountsAccount = Account::factory()->create([
        'name' => 'Both Applications Account',
        'applications' => [ApplicationEnum::Protego, ApplicationEnum::Wheel2Web],
    ]);

    // Create user with accounts
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'is_internal' => false,
    ]);

    $user->accounts()->attach([$protegoAccount->id, $wheel2webAccount->id]);

    // Update user via API
    $userData = [
        'users' => [
            [
                'email' => 'test@example.com',
                'name' => 'Updated User',
                'roles' => [],
                'accounts' => [$protegoAccount->uuid, $wheel2webAccount->uuid],
            ],
        ],
    ];

    // Mock the ApplicationService to verify correct applications are called
    $mockService = Mockery::mock(ApplicationApiService::class);
    $mockService->shouldReceive('pushUser')
        ->twice()
        ->with(Mockery::on(function ($user) {
            return $user instanceof \App\Models\User && $user->email === 'test@example.com';
        }))
        ->andReturnUsing(function ($user) {
            // Verify user has access to both applications
            expect($user->canAccessApplication(ApplicationEnum::Protego))->toBeTrue();
            expect($user->canAccessApplication(ApplicationEnum::Wheel2Web))->toBeTrue();
        });

    $this->app->instance(ApplicationApiService::class, $mockService);

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Process the queue to execute the dispatched jobs
    // Since we're using Bus::fake(), we need to manually execute the jobs
    $dispatchedJobs = Bus::dispatched(SyncUserWithApplications::class);
    foreach ($dispatchedJobs as $job) {
        $job->handle(app(ApplicationApiService::class));
    }

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
});

test('it only syncs accounts to correct applications', function () {
    Bus::fake();
    Http::fake();

    // Create account with specific applications
    $account = Account::factory()->create([
        'name' => 'Test Account',
        'applications' => [ApplicationEnum::Protego],
    ]);

    // Update account via API
    $accountData = [
        'accounts' => [
            [
                'name' => 'Test Account',
                'short_name' => 'updated',
            ],
        ],
    ];

    // Mock the ApplicationService to verify correct applications are called
    $mockService = Mockery::mock(ApplicationApiService::class);
    $mockService->shouldReceive('pushAccount')
        ->twice()
        ->with(Mockery::on(function ($account) {
            return $account instanceof \App\Models\Account && $account->name === 'Test Account';
        }))
        ->andReturnUsing(function ($account) {
            // Verify account only has Protego application
            $applications = $account->getApplications();
            expect($applications)->toContain(ApplicationEnum::Protego);
            expect($applications)->not->toContain(ApplicationEnum::Wheel2Web);
        });

    $this->app->instance(ApplicationApiService::class, $mockService);

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Process the queue to execute the dispatched jobs
    $dispatchedJobs = Bus::dispatched(SyncAccountWithApplications::class);
    foreach ($dispatchedJobs as $job) {
        $job->handle(app(ApplicationApiService::class));
    }

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('internal users are synced to all applications', function () {
    Bus::fake();

    // Create internal user
    $user = User::factory()->create([
        'name' => 'Internal User',
        'email' => 'internal@example.com',
        'is_internal' => true,
    ]);

    // Update user via API
    $userData = [
        'users' => [
            [
                'email' => 'internal@example.com',
                'name' => 'Internal User',
                'is_internal' => true,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify user has access to all applications
    expect($user->canAccessApplication(ApplicationEnum::Protego))->toBeTrue();
    expect($user->canAccessApplication(ApplicationEnum::Wheel2Web))->toBeTrue();

    // Verify getApplications returns all applications
    $applications = $user->getApplications();
    expect($applications)->toContain(ApplicationEnum::Protego);
    expect($applications)->toContain(ApplicationEnum::Wheel2Web);
});

test('non internal users only access applications through accounts', function () {
    Bus::fake();

    // Create non-internal user with no accounts
    $user = User::factory()->create([
        'name' => 'External User',
        'email' => 'external@example.com',
        'is_internal' => false,
    ]);

    // Update user via API
    $userData = [
        'users' => [
            [
                'email' => 'external@example.com',
                'name' => 'External User',
                'is_internal' => false,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify user has no access to applications
    expect($user->canAccessApplication(ApplicationEnum::Protego))->toBeFalse();
    expect($user->canAccessApplication(ApplicationEnum::Wheel2Web))->toBeFalse();

    // Verify getApplications returns empty array
    $applications = $user->getApplications();
    expect($applications)->toBeEmpty();
});

test('user application payload filters correctly', function () {
    // Create accounts with different applications
    $protegoAccount = Account::factory()->create([
        'name' => 'Protego Account',
        'applications' => [ApplicationEnum::Protego],
    ]);

    $wheel2webAccount = Account::factory()->create([
        'name' => 'Wheel2Web Account',
        'applications' => [ApplicationEnum::Wheel2Web],
    ]);

    // Create user with both accounts
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'mobile' => '+1234567890',
        'is_internal' => false,
    ]);

    $user->accounts()->attach([$protegoAccount->id, $wheel2webAccount->id]);

    // Test payload for Protego application
    $protegoPayload = $user->applicationPayload(ApplicationEnum::Protego);
    expect($protegoPayload)->toHaveKey('accounts');
    expect($protegoPayload['accounts'])->toContain($protegoAccount->uuid);
    expect($protegoPayload['accounts'])->not->toContain($wheel2webAccount->uuid);

    // Test payload for Wheel2Web application
    $wheel2webPayload = $user->applicationPayload(ApplicationEnum::Wheel2Web);
    expect($wheel2webPayload)->toHaveKey('accounts');
    expect($wheel2webPayload['accounts'])->toContain($wheel2webAccount->uuid);
    expect($wheel2webPayload['accounts'])->not->toContain($protegoAccount->uuid);
});

test('account application payload works correctly', function () {
    // Create account with specific applications
    $account = Account::factory()->create([
        'name' => 'Test Account',
        'short_name' => 'test',
        'applications' => [ApplicationEnum::Protego, ApplicationEnum::Wheel2Web],
    ]);

    $payload = $account->applicationPayload();

    expect($payload)->toHaveKey('uuid');
    expect($payload)->toHaveKey('name');
    expect($payload)->toHaveKey('short_name');
    expect($payload)->toHaveKey('deleted_at');

    expect($payload['name'])->toBe('Test Account');
    expect($payload['short_name'])->toBe('test');
});

test('it handles soft deleted users correctly in application filtering', function () {
    Bus::fake();

    // Create user and soft delete
    $user = User::factory()->create([
        'name' => 'Deleted User',
        'email' => 'deleted@example.com',
        'is_internal' => false,
    ]);

    $user->delete();

    // Update user via API (restore)
    $userData = [
        'users' => [
            [
                'email' => 'deleted@example.com',
                'name' => 'Restored User',
                'deleted_at' => null,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify user was restored
    $this->assertDatabaseHas('users', [
        'email' => 'deleted@example.com',
        'deleted_at' => null,
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it handles soft deleted accounts correctly in application filtering', function () {
    Bus::fake();

    // Create account and soft delete
    $account = Account::factory()->create([
        'name' => 'Deleted Account',
        'applications' => [ApplicationEnum::Protego],
    ]);

    $account->delete();

    // Update account via API (restore)
    $accountData = [
        'accounts' => [
            [
                'name' => 'Deleted Account',
                'short_name' => 'restored',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Verify account was restored
    $this->assertDatabaseHas('accounts', [
        'name' => 'Deleted Account',
        'deleted_at' => null,
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class);
});
