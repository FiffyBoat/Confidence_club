<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_generate_missing_receipts_from_receipts_page(): void
    {
        Storage::fake('public');

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-800',
            'full_name' => 'Ama Osei',
            'phone' => '0200000800',
            'email' => 'ama@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for April 2026',
            'amount' => 50,
            'payment_method' => 'cash',
            'transaction_date' => '2026-04-01',
            'recorded_by' => $treasurer->id,
        ]);

        $income = Income::create([
            'source' => 'Hall Donation',
            'amount' => 100,
            'description' => 'General income',
            'transaction_date' => '2026-04-02',
            'recorded_by' => $treasurer->id,
        ]);

        $loan = Loan::create([
            'member_id' => $member->id,
            'principal' => 500,
            'interest_rate' => 10,
            'total_payable' => 550,
            'balance' => 450,
            'issue_date' => '2026-03-01',
            'due_date' => '2026-08-01',
            'status' => 'active',
            'recorded_by' => $treasurer->id,
        ]);

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => 100,
            'payment_date' => '2026-04-03',
            'recorded_by' => $treasurer->id,
        ]);

        $indexResponse = $this->actingAs($treasurer)->get(route('receipts.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Generate Missing (3)', false);

        $response = $this->actingAs($treasurer)->post(route('receipts.generate-missing'));

        $response->assertRedirect(route('receipts.index'));
        $response->assertSessionHas('status', 'Generated 3 missing receipts.');

        $this->assertDatabaseHas('receipts', [
            'reference_type' => Receipt::TYPE_CONTRIBUTION,
            'reference_id' => $contribution->id,
        ]);

        $this->assertDatabaseHas('receipts', [
            'reference_type' => Receipt::TYPE_INCOME,
            'reference_id' => $income->id,
        ]);

        $this->assertDatabaseHas('receipts', [
            'reference_type' => Receipt::TYPE_LOAN_REPAYMENT,
            'reference_id' => $repayment->id,
        ]);

        $this->assertSame(3, Receipt::count());
    }
}
