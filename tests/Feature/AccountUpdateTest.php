<?php

use App\Jobs\SyncAccountWithApplications;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;

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


test('it can create new accounts via api', function () {
    Bus::fake();
    Queue::fake();

    $accountData = [
        'accounts' => [
            [
                'name' => 'Test Account',
                'short_name' => 'test',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'accounts' => [
                [
                    'uuid',
                    'name',
                ],
            ],
        ]);

    // Verify account was created
    $this->assertDatabaseHas('accounts', [
        'name' => 'Test Account',
        'short_name' => 'test',
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class);
});

test('it can update existing accounts via api', function () {
    Bus::fake();
    Queue::fake();

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

    // Verify account was updated
    $account->refresh();
    expect($account->short_name)->toBe('updated');

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class);
});

test('it generates short name when not provided', function () {
    Bus::fake();

    $accountData = [
        'accounts' => [
            [
                'name' => 'Very Long Account Name That Needs Shortening',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Verify account was created with generated short name
    $this->assertDatabaseHas('accounts', [
        'name' => 'Very Long Account Name That Needs Shortening',
    ]);

    $account = Account::where('name', 'Very Long Account Name That Needs Shortening')->first();
    expect($account->short_name)->not->toBeNull();
    expect($account->short_name)->not->toBe('Very Long Account Name That Needs Shortening');
});

test('it handles multiple accounts in single request', function () {
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
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'accounts');

    // Verify both accounts were created
    $this->assertDatabaseHas('accounts', ['name' => 'Account One']);
    $this->assertDatabaseHas('accounts', ['name' => 'Account Two']);

    // Verify sync jobs were dispatched for both accounts
    Bus::assertDispatchedTimes(SyncAccountWithApplications::class, 2);
});

test('it handles empty accounts array', function () {
    $accountData = [
        'accounts' => [],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200)
        ->assertJsonCount(0, 'accounts');
});

test('it requires authentication', function () {
    $accountData = [
        'accounts' => [
            [
                'name' => 'Test Account',
                'short_name' => 'test',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(401);
});

test('it requires valid client authorization', function () {
    $accountData = [
        'accounts' => [
            [
                'name' => 'Test Account',
                'short_name' => 'test',
            ],
        ],
    ];

    // Use invalid token
    $response = $this->postJson('/api/accounts', $accountData, [
        'Authorization' => 'Bearer invalid-token',
    ]);

    $response->assertStatus(401);
});

test('it handles duplicate account names correctly', function () {
    Bus::fake();

    // Create initial account
    $account = Account::factory()->create([
        'name' => 'Duplicate Account',
        'short_name' => 'original',
    ]);

    $accountData = [
        'accounts' => [
            [
                'name' => 'Duplicate Account',
                'short_name' => 'updated',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    $response->assertStatus(200);

    // Verify only one account exists with this name
    expect(Account::where('name', 'Duplicate Account')->count())->toBe(1);

    // Verify the account was updated, not duplicated
    $account->refresh();
    expect($account->short_name)->toBe('updated');
});

test('it handles missing required fields gracefully', function () {
    $accountData = [
        'accounts' => [
            [
                // Missing name
                'short_name' => 'test',
            ],
        ],
    ];

    $response = $this->postJson('/api/accounts', $accountData);

    // Should fail validation as name is required for updateOrCreate
    $response->assertStatus(422);
});