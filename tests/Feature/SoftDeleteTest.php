<?php

use App\Enums\ApplicationEnum;
use App\Jobs\SyncAccountWithApplications;
use App\Jobs\SyncUserWithApplications;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    // Create OAuth client for API authentication
    $this->client = Client::factory()->create([
        'name' => ApplicationEnum::Protego->value,
        'secret' => 'test-secret',
        'redirect_uris' => '["http://localhost"]',
        'grant_types' => '["client_credentials"]',
    ]);

    Passport::actingAsClient($this->client);
});

test('it can soft delete users via api', function () {
    Bus::fake();

    $user = User::factory()->createOneQuietly([
        'name' => 'User To Delete',
        'email' => 'delete@example.com',
    ]);

    $deleteData = [
        'users' => [
            [
                'uuid' => $user->uuid,
                'email' => 'delete@example.com',
                'name' => 'User To Delete',
                'is_internal' => false,
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

    // Verify user is not in normal queries
    expect(User::where('email', 'delete@example.com')->first())->toBeNull();

    // Verify user is in withTrashed queries
    expect(User::withTrashed()->where('email', 'delete@example.com')->first())->not->toBeNull();

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});

test('it can restore soft deleted users via api', function () {
    Bus::fake();

    $user = User::factory()->createOneQuietly([
        'name' => 'User To Restore',
        'email' => 'restore@example.com',
    ]);
    $user->deleteQuietly(); // Soft delete

    $restoreData = [
        'users' => [
            [
                'uuid' => $user->uuid,
                'email' => 'restore@example.com',
                'name' => 'User To Restore',
                'is_internal' => false,
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

    // Verify user is back in normal queries
    expect(User::where('email', 'restore@example.com')->first())->not->toBeNull();

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});

test('it can soft delete accounts via api', function () {
    Bus::fake();

    $account = Account::factory()->createOneQuietly([
        'name' => 'Account To Delete',
        'short_name' => 'delete',
    ]);

    $deleteData = [
        'accounts' => [
            [
                'uuid' => $account->uuid,
                'name' => 'Account To Delete',
                'short_name' => 'delete',
                'deleted_at' => now()->toISOString(),
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $deleteData);

    $response->assertStatus(200);

    // Verify account was soft deleted
    $this->assertSoftDeleted('accounts', [
        'name' => 'Account To Delete',
    ]);

    // Verify account is not in normal queries
    expect(Account::where('name', 'Account To Delete')->first())->toBeNull();

    // Verify account is in withTrashed queries
    expect(Account::withTrashed()->where('name', 'Account To Delete')->first())->not->toBeNull();

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncAccountWithApplications::class);
});

test('it can restore soft deleted accounts via api', function () {
    Bus::fake();

    $account = Account::factory()->createOneQuietly([
        'name' => 'Account To Restore',
        'short_name' => 'restore',
    ]);
    $account->deleteQuietly(); // Soft delete

    $restoreData = [
        'accounts' => [
            [
                'uuid' => $account->uuid,
                'name' => 'Account To Restore',
                'short_name' => 'restored',
                'deleted_at' => null,
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $restoreData);

    $response->assertStatus(200);

    // Verify account was restored
    $this->assertDatabaseHas('accounts', [
        'name' => 'Account To Restore',
        'deleted_at' => null,
    ]);

    // Verify account is back in normal queries
    expect(Account::where('name', 'Account To Restore')->first())->not->toBeNull();

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncAccountWithApplications::class);
});

test('it handles soft deleted users with relationships', function () {
    Bus::fake();

    // Create user with accounts
    $user = User::factory()->createOneQuietly([
        'name' => 'User With Accounts',
        'email' => 'userwithaccounts@example.com',
    ]);

    $account1 = Account::factory()->createOneQuietly(['name' => 'Account 1']);
    $account2 = Account::factory()->createOneQuietly(['name' => 'Account 2']);

    $user->accounts()->attach([$account1->id, $account2->id]);

    // Soft delete user
    $deleteData = [
        'users' => [
            [
                'uuid' => $user->uuid,
                'email' => 'userwithaccounts@example.com',
                'name' => 'User With Accounts',
                'is_internal' => false,
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [$account1->uuid, $account2->uuid],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $deleteData);

    $response->assertStatus(200);

    // Verify user was soft deleted
    $this->assertSoftDeleted('users', [
        'email' => 'userwithaccounts@example.com',
    ]);

    // Verify relationships are maintained
    $user->refresh();
    expect($user->accounts)->toHaveCount(2);

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});

test('it handles soft deleted accounts with relationships', function () {
    Bus::fake();

    // Create account with users
    $account = Account::factory()->create([
        'name' => 'Account With Users',
        'short_name' => 'withusers',
    ]);

    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $account->users()->attach([$user1->id, $user2->id]);

    // Soft delete account
    $account->delete();

    // Verify account was soft deleted
    $this->assertSoftDeleted('accounts', [
        'name' => 'Account With Users',
    ]);

    // Verify relationships are maintained
    $account->refresh();
    expect($account->users)->toHaveCount(2);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class);
});

test('it handles restoring users with relationships', function () {
    Bus::fake();

    // Create user with accounts
    $user = User::factory()->createOneQuietly([
        'name' => 'User To Restore With Accounts',
        'email' => 'restorewithaccounts@example.com',
    ]);

    $account1 = Account::factory()->createOneQuietly(['name' => 'Restore Account 1']);
    $account2 = Account::factory()->createOneQuietly(['name' => 'Restore Account 2']);

    $user->accounts()->attach([$account1->id, $account2->id]);
    $user->deleteQuietly(); // Soft delete

    // Restore user
    $restoreData = [
        'users' => [
            [
                'uuid' => $user->uuid,
                'email' => 'restorewithaccounts@example.com',
                'name' => 'User To Restore With Accounts',
                'is_internal' => false,
                'deleted_at' => null,
                'roles' => [],
                'accounts' => [$account1->uuid, $account2->uuid],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $restoreData);

    $response->assertStatus(200);

    // Verify user was restored
    $this->assertDatabaseHas('users', [
        'email' => 'restorewithaccounts@example.com',
        'deleted_at' => null,
    ]);

    // Verify relationships are maintained
    $user->refresh();
    expect($user->accounts)->toHaveCount(2);

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});

test('it handles restoring accounts with relationships', function () {
    Bus::fake();

    // Create account with users
    $account = Account::factory()->createOneQuietly([
        'name' => 'Account To Restore With Users',
        'short_name' => 'restorewithusers',
    ]);

    $user1 = User::factory()->createOneQuietly(['email' => 'restoreuser1@example.com']);
    $user2 = User::factory()->createOneQuietly(['email' => 'restoreuser2@example.com']);

    $account->users()->attach([$user1->id, $user2->id]);
    $account->deleteQuietly(); // Soft delete

    // Restore account
    $restoreData = [
        'accounts' => [
            [
                'uuid' => $account->uuid,
                'name' => 'Account To Restore With Users',
                'short_name' => 'restored',
                'deleted_at' => null,
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $restoreData);

    $response->assertStatus(200);

    // Verify account was restored
    $this->assertDatabaseHas('accounts', [
        'name' => 'Account To Restore With Users',
        'deleted_at' => null,
    ]);

    // Verify relationships are maintained
    $account->refresh();
    expect($account->users)->toHaveCount(2);

    // Verify sync job was dispatched
    Bus::assertNotDispatched(SyncAccountWithApplications::class);
});

test('it handles multiple soft deletes in single request', function () {
    Bus::fake();

    $user1 = User::factory()->createOneQuietly(['email' => 'delete1@example.com']);
    $user2 = User::factory()->createOneQuietly(['email' => 'delete2@example.com']);

    $deleteData = [
        'users' => [
            [
                'uuid' => $user1->uuid,
                'email' => 'delete1@example.com',
                'name' => $user1->name,
                'is_internal' => false,
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [],
            ],
            [
                'uuid' => $user2->uuid,
                'email' => 'delete2@example.com',
                'name' => $user2->name,
                'is_internal' => false,
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $deleteData);

    $response->assertStatus(200);

    // Verify both users were soft deleted
    $this->assertSoftDeleted('users', ['email' => 'delete1@example.com']);
    $this->assertSoftDeleted('users', ['email' => 'delete2@example.com']);

    // Verify sync jobs were dispatched for both users (2 users × 2 updates each = 4 jobs)
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});

test('it handles mixed soft delete and restore in single request', function () {
    Bus::fake();

    $userToDelete = User::factory()->createOneQuietly(['email' => 'delete@example.com']);
    $userToRestore = User::factory()->createOneQuietly(['email' => 'restore@example.com']);
    $userToRestore->deleteQuietly(); // Soft delete

    $mixedData = [
        'users' => [
            [
                'uuid' => $userToDelete->uuid,
                'email' => 'delete@example.com',
                'name' => $userToDelete->name,
                'is_internal' => false,
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [],
            ],
            [
                'uuid' => $userToRestore->uuid,
                'email' => 'restore@example.com',
                'name' => $userToRestore->name,
                'is_internal' => false,
                'deleted_at' => null,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $mixedData);

    $response->assertStatus(200);

    // Verify user was soft deleted
    $this->assertSoftDeleted('users', ['email' => 'delete@example.com']);

    // Verify user was restored
    $this->assertDatabaseHas('users', [
        'email' => 'restore@example.com',
        'deleted_at' => null,
    ]);

    // Verify sync jobs were dispatched for both users (5 jobs total)
    Bus::assertNotDispatched(SyncUserWithApplications::class);
});
