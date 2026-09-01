<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_registration_creates_and_authenticates_the_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'PromoHub User',
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'PromoHub User',
            'email' => 'user@example.com',
        ]);
    }

    public function test_valid_credentials_authenticate_the_user(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }
}
