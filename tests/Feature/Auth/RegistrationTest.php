<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_and_unused_breeze_routes_are_disabled(): void
    {
        $this->get('/register')->assertStatus(404);
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(404);

        $this->get('/forgot-password')->assertStatus(404);
        $this->post('/forgot-password', ['email' => 'test@example.com'])->assertStatus(404);
        $this->get('/reset-password/dummy-token')->assertStatus(404);
        $this->post('/reset-password')->assertStatus(404);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/verify-email')->assertStatus(404);
        $this->actingAs($user)->get('/confirm-password')->assertStatus(404);
    }
}
