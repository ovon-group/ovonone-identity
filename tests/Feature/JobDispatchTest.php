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
        'revoked' => false,
    ]);

    Passport::actingAsClient($this->client);
});

test('it dispatches sync job when user is created', function () {
    Bus::fake();

    $user = User::factory()->create([
        'name' => 'New User',
        'email' => 'new@example.com',
    ]);

    $user->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->accounts->contains(fn ($account) => $account->applications->contains(ApplicationEnum::Protego)
            );
    });
});

test('it dispatches sync job when user is updated', function () {
    Bus::fake();

    $user = User::factory()->createOneQuietly([
        'name' => 'Original Name',
        'email' => 'update@example.com',
    ]);

    $user->update([
        'name' => 'Updated Name',
    ]);

    // Verify sync job was dispatched
    $user->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->accounts->contains(fn ($account) => $account->applications->contains(ApplicationEnum::Protego)
            );
    });
});

test('it dispatches sync job when user is soft deleted', function () {
    Bus::fake();

    $user = User::factory()->createOneQuietly([
        'name' => 'User To Delete',
        'email' => 'delete@example.com',
    ]);

    $user->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));

    $user->delete();

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->accounts->contains(fn ($account) => $account->applications->contains(ApplicationEnum::Protego)
            );
    });
});

test('it dispatches sync job when user is restored', function () {
    Bus::fake();

    $user = User::factory()->createOneQuietly([
        'name' => 'User To Restore',
        'email' => 'restore@example.com',
    ]);
    $user->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));
    $user->deleteQuietly(); // Soft delete

    $user->restore();

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncUserWithApplications::class, function ($job) use ($user) {
        return $job->user->id === $user->id
            && $job->user->accounts->contains(fn ($account) => $account->applications->contains(ApplicationEnum::Protego)
            )
            && $job->user->deleted_at === null;
    });
});

test('it dispatches sync job when account is created', function () {
    Bus::fake();

    Account::factory()->create(['applications' => [ApplicationEnum::Protego]]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) {
        return $job->account->applications->contains(ApplicationEnum::Protego);
    });
});

test('it dispatches sync job when account is updated', function () {
    Bus::fake();

    $account = Account::factory()->createOneQuietly([
        'name' => 'Original Account',
        'short_name' => 'original',
        'applications' => [ApplicationEnum::Protego],
    ]);

    $account->update([
        'name' => 'Updated Account',
        'short_name' => 'updated',
    ]);

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('it dispatches sync job when account is soft deleted', function () {
    Bus::fake();

    $account = Account::factory()->createOneQuietly([
        'name' => 'Account To Delete',
        'short_name' => 'delete',
        'applications' => [ApplicationEnum::Protego],
    ]);

    // Soft delete the account
    $account->delete();

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id;
    });
});

test('it dispatches sync job when account is restored', function () {
    Bus::fake();

    $account = Account::factory()->createOneQuietly([
        'name' => 'Account To Restore',
        'short_name' => 'restore',
    ]);
    $account->deleteQuietly(); // Soft delete

    $account->restore();

    // Verify sync job was dispatched
    Bus::assertDispatched(SyncAccountWithApplications::class, function ($job) use ($account) {
        return $job->account->id === $account->id
            && $job->account->deleted_at === null;
    });
});

test('it dispatches multiple sync jobs for multiple users', function () {
    Bus::fake();

    $user1 = User::factory()->create([
        'name' => 'User One',
        'email' => 'user1@example.com',
    ]);
    $user2 = User::factory()->create([
        'name' => 'User Two',
        'email' => 'user2@example.com',
    ]);
    $user3 = User::factory()->create([
        'name' => 'User Three',
        'email' => 'user3@example.com',
    ]);
    $user1->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));
    $user2->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));
    $user3->accounts()->attach(Account::factory()->create(['applications' => [ApplicationEnum::Protego]]));

    // Verify sync jobs were dispatched for all users
    Bus::assertDispatchedTimes(SyncUserWithApplications::class, 3);
});
