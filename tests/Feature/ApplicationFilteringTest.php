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
    $this->client = Client::factory()->create([
        'name' => ApplicationEnum::Protego->value,
        'secret' => 'test-secret',
        'redirect_uris' => '["http://localhost"]',
        'grant_types' => '["client_credentials"]',
        'revoked' => false,
    ]);

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

    $user->update([
        'name' => 'Updated User',
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->accounts->contains(fn ($account) =>
                $account->applications->contains(ApplicationEnum::Protego)
            )
            && $job->user->accounts->contains(fn ($account) =>
                $account->applications->contains(ApplicationEnum::Wheel2Web)
            );
    });
});

test('it only syncs accounts to correct applications', function () {
    Bus::fake();
    Http::fake();

    // Create account with specific applications
    $account = Account::factory()->createOneQuietly([
        'name' => 'Test Account',
        'applications' => [ApplicationEnum::Protego],
    ]);

    $account->update([
        'name' => 'Updated Account',
        'applications' => [ApplicationEnum::Protego, ApplicationEnum::Wheel2Web],
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id
            && $job->account->applications->contains(ApplicationEnum::Protego)
            && $job->account->applications->contains(ApplicationEnum::Wheel2Web);
    });
});

test('internal users are synced to all applications', function () {
    Bus::fake();

    $account = Account::factory()->createQuietly([
        'name' => 'Test Dealership',
        'applications' => [ApplicationEnum::Protego, ApplicationEnum::Wheel2Web],
    ]);

    // Create internal user
    $user = User::factory()->create([
        'name' => 'Internal User',
        'email' => 'internal@example.com',
        'is_internal' => true,
    ]);

    $user->accounts()->attach($account->id);

    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->is_internal === true
            && $job->user->accounts->contains(fn ($account) =>
                $account->applications->contains(ApplicationEnum::Protego)
            )
            && $job->user->accounts->contains(fn ($account) =>
                $account->applications->contains(ApplicationEnum::Wheel2Web)
            );
    });
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
