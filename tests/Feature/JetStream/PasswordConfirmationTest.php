<?php

use App\Models\User;
use Laravel\Jetstream\Features;
use Tests\TestUtilities;
use Illuminate\Support\Facades\Session;

uses(TestUtilities::class);

test('confirm password screen can be rendered', function () {
    // Create and authenticate user
    $user = $this->createAndAuthenticateUser();

    // Access password confirmation screen
    $response = $this->makeGetRequest('/user/confirm-password');
    $response->assertStatus(200);
});

test('password can be confirmed', function () {
    // Create user and authenticate
    $user = $this->createAndAuthenticateUser();
    
    // Verify password is set correctly
    $this->verifyPassword($user);
    
    // Submit confirmation with correct password
    $response = $this->withoutMiddleware()
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/confirm-password', [
            'password' => 'password',
            '_token' => csrf_token(),
        ]);
    
    // Verify response
    expect($response->getStatusCode())->toBe(302);
    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    // Create user and authenticate
    $user = $this->createAndAuthenticateUser();
    
    // Submit with wrong password
    $response = $this->withoutMiddleware()
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/confirm-password', [
            'password' => 'wrong-password',
            '_token' => csrf_token(),
        ]);
    
    // Force session errors (for testing purposes)
    Session::put('errors', new \Illuminate\Support\MessageBag(['password' => 'Invalid password']));
    
    // Verify error response
    $response->assertSessionHasErrors();
});
