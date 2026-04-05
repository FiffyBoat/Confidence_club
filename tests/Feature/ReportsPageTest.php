<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_view_reports_bar_chart(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($treasurer)->get('/reports');

        $response->assertOk();
        $response->assertSee('Monthly Snapshot (Last 6)');
        $response->assertSee('reports-bar-chart', false);
    }
}
