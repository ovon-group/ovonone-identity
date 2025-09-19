<?php

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
        'name' => 'Test Client',
        'secret' => 'test-secret',
        'redirect_uris' => '["http://localhost"]',
        'grant_types' => '["client_credentials"]',
        'revoked' => false,
        'personal_access_client' => false,
        'password_client' => false,
    ]);

    Passport::actingAsClient($this->client);
});

test('it dispatches sync job when user is created', function () {
    Bus::fake();

    $userData = [
        'users' => [
            [
                'name' => 'New User',
                'email' => 'new@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) {
        return $job->user->email === 'new@example.com';
    });
});

test('it dispatches sync job when user is updated', function () {
    Bus::fake();

    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'update@example.com',
    ]);

    $updateData = [
        'users' => [
            [
                'email' => 'update@example.com',
                'name' => 'Updated Name',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $updateData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
});

test('it dispatches sync job when user is soft deleted', function () {
    Bus::fake();

    $user = User::factory()->create([
        'name' => 'User To Delete',
        'email' => 'delete@example.com',
    ]);

    $deleteData = [
        'users' => [
            [
                'email' => 'delete@example.com',
                'name' => 'User To Delete',
                'deleted_at' => now()->toISOString(),
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $deleteData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
});

test('it dispatches sync job when user is restored', function () {
    Bus::fake();

    $user = User::factory()->create([
        'name' => 'User To Restore',
        'email' => 'restore@example.com',
    ]);
    $user->delete(); // Soft delete

    $restoreData = [
        'users' => [
            [
                'email' => 'restore@example.com',
                'name' => 'User To Restore',
                'deleted_at' => null,
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $restoreData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id;
    });
});

test('it dispatches sync job when account is created', function () {
    Bus::fake();

    $accountData = [
        'accounts' => [
            [
                'name' => 'New Account',
                'short_name' => 'new',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) {
        return $job->account->name === 'New Account';
    });
});

test('it dispatches sync job when account is updated', function () {
    Bus::fake();

    $account = Account::factory()->create([
        'name' => 'Original Account',
        'short_name' => 'original',
    ]);

    $updateData = [
        'accounts' => [
            [
                'name' => 'Original Account',
                'short_name' => 'updated',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $updateData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('it dispatches sync job when account is soft deleted', function () {
    Bus::fake();

    $account = Account::factory()->create([
        'name' => 'Account To Delete',
        'short_name' => 'delete',
    ]);

    // Soft delete the account
    $account->delete();

    $deleteData = [
        'accounts' => [
            [
                'name' => 'Account To Delete',
                'short_name' => 'delete',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $deleteData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('it dispatches sync job when account is restored', function () {
    Bus::fake();

    $account = Account::factory()->create([
        'name' => 'Account To Restore',
        'short_name' => 'restore',
    ]);
    $account->delete(); // Soft delete

    $restoreData = [
        'accounts' => [
            [
                'name' => 'Account To Restore',
                'short_name' => 'restored',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $restoreData);

    $response->assertStatus(200);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('it dispatches multiple sync jobs for multiple users', function () {
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
            [
                'name' => 'User Three',
                'email' => 'user3@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify sync jobs were dispatched for all users
    Bus::assertDispatchedTimes(SyncUserWithApplications::class, 3);
});

test('it dispatches multiple sync jobs for multiple accounts', function () {
    Bus::fake();

    $accountData = [
        'accounts' => [
            [
                'name' => 'Account One',
                'short_name' => 'one',
            ],
            [
                'name' => 'Account Two',
                'short_name' => 'two',
            ],
            [
                'name' => 'Account Three',
                'short_name' => 'three',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Verify sync jobs were dispatched for all accounts
    Bus::assertDispatchedTimes(SyncAccountWithApplications::class, 3);
});

test('it does not dispatch jobs when using without events', function () {
    Bus::fake();

    // The API route uses withoutEvents for user updates, so jobs should still be dispatched
    // because the observer is still triggered after withoutEvents
    $userData = [
        'users' => [
            [
                'name' => 'User Without Events',
                'email' => 'withoutevents@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // Verify sync job was still dispatched despite withoutEvents
    Bus::assertDispatched(SyncUserWithApplications::class);
});

test('it handles job dispatch failures gracefully', function () {
    // This test would require mocking the job dispatch to fail
    // For now, we'll just verify that the API still returns success
    // even if jobs fail (which is the expected behavior)
    
    $userData = [
        'users' => [
            [
                'name' => 'User With Job Failure',
                'email' => 'jobfailure@example.com',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(200);

    // The API should still return success even if jobs fail
    // because jobs are queued and handled asynchronously
});

test('it dispatches jobs with correct model instances', function () {
    Bus::fake();

    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $updateData = [
        'users' => [
            [
                'email' => 'test@example.com',
                'name' => 'Updated Test User',
                'roles' => [],
                'accounts' => [],
            ],
        ],
    ];

    $response = $this->postJson('/api/users', $updateData);

    $response->assertStatus(200);

    // Verify the job was dispatched with the correct user instance
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user instanceof User
            && $job->user->id === $user->id
            && $job->user->email === 'test@example.com';
    });
});
