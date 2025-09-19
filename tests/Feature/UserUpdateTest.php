<?php

use App\Jobs\SyncUserWithApplications;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create OAuth client for API authentication
    $this->client = Client::factory()->create([
        'name' => 'Test Client',
        'secret' => 'test-secret',
        'redirect_uris' => '["http://localhost"]',
        'grant_types' => '["client_credentials"]',
        'revoked' => false,
    ]);

    // Skip authentication for tests that need to test unauthenticated access
    $unauthenticatedTests = ['it requires authentication', 'it requires valid client authorization'];
    $shouldLogin = collect($unauthenticatedTests)
        ->filter(fn ($test) => str_contains($this->name(), Str::snake($test)))
        ->isEmpty();

    if ($shouldLogin) {
        Passport::actingAsClient($this->client);
    }
});

test('it can create new users via api', function () {
    Bus::fake();
    Queue::fake();

    // Create the user role
    $userRole = Role::create(['name' => 'user', 'guard_name' => 'api', 'app' => 'protego']);

    $userData = [
        'users' => [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'mobile' => '+1234567890',
                'is_internal' => false,
                'roles' => ['user'],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'users' => [
                [
                    'uuid',
                    'email',
                    'mobile',
                    'name',
                ],
            ],
        ]);

    // Verify user was created
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile' => '+1234567890',
        'is_internal' => false,
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it can update existing users via api', function () {
    Bus::fake();
    Queue::fake();

    // Create the admin role
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api', 'app' => 'protego']);

    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'mobile' => '+1111111111',
        'is_internal' => false,
    ]);

    $updateData = [
        'users' => [
            [
                'email' => 'original@example.com',
                'name' => 'Updated Name',
                'mobile' => '+2222222222',
                'is_internal' => true,
                'roles' => ['admin'],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $updateData);

    $response->assertStatus(200);

    // Verify user was updated
    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->mobile)->toBe('+2222222222');
    expect($user->is_internal)->toBeTrue();

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it can soft delete users via api', function () {
    Bus::fake();
    Queue::fake();

    $user = User::factory()->create([
        'name' => 'To Be Deleted',
        'email' => 'delete@example.com',
    ]);

    $deleteData = [
        'users' => [
            [
                'email' => 'delete@example.com',
                'name' => 'To Be Deleted',
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $deleteData);

    $response->assertStatus(200);

    // Verify user was soft deleted
    $this->assertSoftDeleted('users', [
        'email' => 'delete@example.com',
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it can restore soft deleted users via api', function () {
    Bus::fake();
    Queue::fake();

    $user = User::factory()->create([
        'name' => 'Restored User',
        'email' => 'restore@example.com',
    ]);
    $user->delete(); // Soft delete

    $restoreData = [
        'users' => [
            [
                'email' => 'restore@example.com',
                'name' => 'Restored User',
                'deleted_at' => null,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $restoreData);

    $response->assertStatus(200);

    // Verify user was restored
    $this->assertDatabaseHas('users', [
        'email' => 'restore@example.com',
        'deleted_at' => null,
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it syncs user roles correctly', function () {
    Bus::fake();

    // Create roles
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api', 'app' => 'protego']);
    $userRole = Role::create(['name' => 'user', 'guard_name' => 'api', 'app' => 'protego']);

    $user = User::factory()->create([
        'email' => 'roles@example.com',
    ]);

    $userData = [
        'users' => [
            [
                'email' => 'roles@example.com',
                'name' => 'Role User',
                'roles' => ['admin', 'user'],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify roles were synced
    $user->refresh();
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('user'))->toBeTrue();
});

test('it syncs user accounts correctly', function () {
    Bus::fake();

    $account1 = Account::factory()->create(['name' => 'Account 1']);
    $account2 = Account::factory()->create(['name' => 'Account 2']);

    $user = User::factory()->create([
        'email' => 'accounts@example.com',
    ]);

    $userData = [
        'users' => [
            [
                'email' => 'accounts@example.com',
                'name' => 'Account User',
                'roles' => [],
                'accounts' => [$account1->uuid, $account2->uuid],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify accounts were synced
    $user->refresh();
    expect($user->accounts)->toHaveCount(2);
    expect($user->accounts->contains($account1))->toBeTrue();
    expect($user->accounts->contains($account2))->toBeTrue();
});

test('it handles users without email using name and mobile', function () {
    Bus::fake();

    $userData = [
        'users' => [
            [
                'name' => 'No Email User',
                'mobile' => '+9999999999',
                'is_internal' => false,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify user was created without email
    $this->assertDatabaseHas('users', [
        'name' => 'No Email User',
        'mobile' => '+9999999999',
        'email' => null,
    ]);
});

test('it requires authentication', function () {
    $userData = [
        'users' => [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(401);
});

test('it requires valid client authorization', function () {
    $userData = [
        'users' => [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    // Use invalid token
    $response = $this->postJson('/api/users', $userData, [
        'Authorization' => 'Bearer invalid-token',
    ]);

    $response->assertStatus(401);
});

test('it handles multiple users in single request', function () {
    Bus::fake();

    $userData = [
        'users' => [
            [
                'name' => 'User One',
                'email' => 'user1@example.com',
                'roles' => [],
                'accounts' => [],
            ],
            [
                'name' => 'User Two',
                'email' => 'user2@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'users');

    // Verify both users were created
    $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
    $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);

    // Verify sync jobs were dispatched for both users (2 users × 1 updates each = 2 jobs)
    Bus::assertDispatchedTimes(SyncUserWithApplications::class, 2);
});

test('it handles empty users array', function () {
    $userData = [
        'users' => [],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200)
        ->assertJsonCount(0, 'users');
});

test('it handles missing required fields gracefully', function () {
    $userData = [
        'users' => [
            [
                // Missing name and email
                'mobile' => '+1234567890',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    // Should fail because name is required in the database
    $response->assertStatus(500);
});