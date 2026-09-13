<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('email')->nullable()->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('users');
});

test('a user can clear their optional email address', function () {
    $user = User::create([
        'username' => 'test-user',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->actingAs($user)
        ->putJson('/api/v1/user/profile-information', [
            'username' => 'test-user',
            'email' => '',
        ])
        ->assertOk();

    expect($user->fresh()->email)->toBeNull();
});

test('changing an email address clears verification and sends a notification', function () {
    Notification::fake();

    $user = User::create([
        'username' => 'test-user',
        'email' => 'old@example.com',
        'email_verified_at' => now(),
        'password' => 'password',
    ]);

    $this->actingAs($user)
        ->putJson('/api/v1/user/profile-information', [
            'username' => 'test-user',
            'email' => 'new@example.com',
        ])
        ->assertOk();

    $updatedUser = $user->fresh();

    expect($updatedUser->email)->toBe('new@example.com')
        ->and($updatedUser->email_verified_at)->toBeNull();

    Notification::assertSentTo($updatedUser, VerifyEmail::class);
});

test('a user can update their password', function () {
    $user = User::create([
        'username' => 'test-user',
        'email' => null,
        'password' => 'old-password',
    ]);

    $this->actingAs($user)
        ->putJson('/api/v1/user/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertOk();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('resetting a password verifies the reset email', function () {
    $user = User::create([
        'username' => 'test-user',
        'email' => 'reset@example.com',
        'password' => 'old-password',
    ]);

    $token = Password::createToken($user);

    $this->postJson('/api/v1/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertOk();

    $updatedUser = $user->fresh();

    expect(Hash::check('new-password', $updatedUser->password))->toBeTrue()
        ->and($updatedUser->email_verified_at)->not->toBeNull();
});

test('a user can delete their account', function () {
    $user = User::create([
        'username' => 'test-user',
        'email' => null,
        'password' => 'password',
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson('/api/v1/user/'.$user->id)->assertNoContent();

    expect(User::find($user->id))->toBeNull();
});
