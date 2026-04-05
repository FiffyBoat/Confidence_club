<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_profile_shows_payments_and_unpaid_dues_statement(): void
    {
        Setting::create(['key' => 'club_start_date', 'value' => '2026-01-01']);
        Setting::create(['key' => 'monthly_dues_amount', 'value' => '50']);

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-500',
            'full_name' => 'Efua Danso',
            'phone' => '0200000500',
            'email' => 'efua@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for January 2026',
            'amount' => 50,
            'payment_method' => 'cash',
            'transaction_date' => '2026-01-01',
            'recorded_by' => $treasurer->id,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'type' => 'Admission Fee',
            'description' => 'Membership admission fee',
            'amount' => 200,
            'payment_method' => 'cash',
            'transaction_date' => '2026-01-05',
            'recorded_by' => $treasurer->id,
        ]);

        $loan = Loan::create([
            'member_id' => $member->id,
            'principal' => 500,
            'interest_rate' => 10,
            'total_payable' => 550,
            'balance' => 450,
            'issue_date' => '2026-02-01',
            'due_date' => '2026-06-01',
            'status' => 'active',
            'recorded_by' => $treasurer->id,
        ]);

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 100,
            'payment_date' => '2026-02-10',
            'recorded_by' => $treasurer->id,
        ]);

        $response = $this->actingAs($treasurer)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee('Payments Done');
        $response->assertSee('Unpaid Monthly Dues');
        $response->assertSee('Admission Fee');
        $response->assertSee('Loan Repayment');
        $response->assertSee('Feb 2026');
    }

    public function test_member_statement_pdf_route_returns_pdf_response(): void
    {
        Setting::create(['key' => 'club_start_date', 'value' => '2026-01-01']);
        Setting::create(['key' => 'monthly_dues_amount', 'value' => '50']);

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-501',
            'full_name' => 'Naa Korkor',
            'phone' => '0200000501',
            'email' => 'naa@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($treasurer)->get(route('members.statement.pdf', $member));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('Content-Type'));
    }
}
