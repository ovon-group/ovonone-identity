<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can create a user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('it can create an account', function () {
    $account = Account::factory()->create([
        'name' => 'Test Account',
        'short_name' => 'test',
    ]);

    expect($account->name)->toBe('Test Account');
    expect($account->short_name)->toBe('test');
});

test('it can soft delete a user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $user->delete();

    expect($user->trashed())->toBeTrue();
    expect(User::where('email', 'test@example.com')->first())->toBeNull();
    expect(User::withTrashed()->where('email', 'test@example.com')->first())->not->toBeNull();
});

test('it can restore a soft deleted user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $user->delete();
    $user->restore();

    expect($user->trashed())->toBeFalse();
    expect(User::where('email', 'test@example.com')->first())->not->toBeNull();
});
