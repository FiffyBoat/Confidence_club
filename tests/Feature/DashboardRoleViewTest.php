<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_sees_dashboard(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($treasurer)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Overview');
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $viewer = User::factory()->create([
            'role' => 'viewer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($viewer)->get('/admin');
        $response->assertForbidden();
    }
}
