<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisabledUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'role' => 'cashier',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
