<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Debug user creation
    expect($user->exists)->toBeTrue();
    expect($user->id)->toBeGreaterThan(0);

    // Skip middleware for testing
    $response = $this->withoutMiddleware()
        ->post('/forgot-password', [
            'email' => $user->email,
        ]);

    // Debug response
    expect($response->getStatusCode())->toBe(302);
    
    Notification::assertSentTo($user, ResetPassword::class);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Skip middleware for testing
    $response = $this->withoutMiddleware()
        ->post('/forgot-password', [
            'email' => $user->email,
        ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
        // Debug token
        expect($notification->token)->not->toBeEmpty();
        
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Skip middleware for testing
    $response = $this->withoutMiddleware()
        ->post('/forgot-password', [
            'email' => $user->email,
        ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        // Debug token and user
        expect($notification->token)->not->toBeEmpty();
        expect($user->email)->not->toBeEmpty();
        
        // Skip middleware for reset password request
        $response = $this->withoutMiddleware()
            ->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');
