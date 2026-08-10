<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseYearFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_index_shows_total_expenses_for_selected_year(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        Expense::create([
            'category' => 'Transport',
            'amount' => 150,
            'description' => 'Bus fare',
            'transaction_date' => '2026-01-10',
            'recorded_by' => $treasurer->id,
        ]);

        Expense::create([
            'category' => 'Printing',
            'amount' => 75,
            'description' => 'Flyers',
            'transaction_date' => '2026-03-05',
            'recorded_by' => $treasurer->id,
        ]);

        Expense::create([
            'category' => 'Hall',
            'amount' => 500,
            'description' => 'Venue payment',
            'transaction_date' => '2025-11-15',
            'recorded_by' => $treasurer->id,
        ]);

        $response = $this->actingAs($treasurer)->get('/expenses?year=2026');

        $response->assertOk();
        $response->assertSee('Total Expenses (2026)');
        $response->assertSee('GHS 225.00');
        $response->assertSee('Transport');
        $response->assertSee('Printing');
        $response->assertDontSee('Hall');
    }
}
