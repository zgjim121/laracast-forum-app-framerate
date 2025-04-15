<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;

/**
 * Common utilities for Laravel tests
 * Solves issues with CSRF, authentication and database transactions
 */
trait TestUtilities
{
    /**
     * Create a post request that bypasses CSRF protection
     */
    public function makePostRequest($uri, $data = [], $headers = [])
    {
        return $this->withoutMiddleware()
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post($uri, array_merge($data, ['_token' => csrf_token()]), $headers);
    }
    
    /**
     * Create a get request that bypasses CSRF protection
     */
    public function makeGetRequest($uri, $headers = [])
    {
        return $this->withoutMiddleware()
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get($uri, $headers);
    }

    /**
     * Create a user and authenticate as that user
     */
    public function createAndAuthenticateUser($attributes = [])
    {
        $user = User::factory()->create($attributes);
        
        // Ensure the user exists in the database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
        
        // Manually log in the user 
        Auth::login($user);
        
        // Double check the auth state
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        
        return $user;
    }
    
    /**
     * Verify a user's password is correctly hashed
     */
    public function verifyPassword($user, $plaintext = 'password')
    {
        $this->assertTrue(Hash::check($plaintext, $user->password));
        return $this;
    }
    
    /**
     * Assert a user exists after an operation
     */
    public function assertUserExists($user)
    {
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertEquals($user->id, $freshUser->id);
        return $freshUser;
    }
    
    /**
     * Assert a user does not exist after an operation
     */
    public function assertUserDeleted($user)
    {
        $this->assertNull($user->fresh());
        return $this;
    }
}

