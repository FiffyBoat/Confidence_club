<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class ReceiptAtomicityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dues_payment_rolls_back_when_receipt_creation_fails(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-700',
            'full_name' => 'Adwoa Mensah',
            'phone' => '0200000700',
            'email' => 'adwoa@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $this->mock(ReceiptService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createForContribution')
                ->once()
                ->andThrow(new RuntimeException('receipt failed'));
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($treasurer)->post(route('dues.store'), [
                'member_id' => $member->id,
                'month' => 4,
                'year' => 2026,
                'amount' => 50,
                'payment_method' => 'cash',
                'transaction_date' => '2026-04-01',
            ]);
            $this->fail('The dues request should have failed when receipt creation failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('receipt failed', $exception->getMessage());
        }

        $this->assertSame(0, Contribution::count());
        $this->assertSame(0, Receipt::count());
    }

    public function test_special_contribution_rolls_back_when_receipt_creation_fails(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-701',
            'full_name' => 'Kojo Addai',
            'phone' => '0200000701',
            'email' => 'kojo@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $this->mock(ReceiptService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createForContribution')
                ->once()
                ->andThrow(new RuntimeException('receipt failed'));
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($treasurer)->post(route('special-contributions.store'), [
                'member_id' => $member->id,
                'description' => 'Fundraising pledge',
                'amount' => 150,
                'payment_method' => 'momo',
                'transaction_date' => '2026-04-02',
            ]);
            $this->fail('The special contribution request should have failed when receipt creation failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('receipt failed', $exception->getMessage());
        }

        $this->assertSame(0, Contribution::count());
        $this->assertSame(0, Receipt::count());
    }

    public function test_member_admission_fee_rolls_back_without_losing_member_when_receipt_creation_fails(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $this->mock(ReceiptService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createForContribution')
                ->once()
                ->andThrow(new RuntimeException('receipt failed'));
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($treasurer)->post(route('members.store'), [
                'membership_id' => 'CCM-702',
                'full_name' => 'Esi Boadu',
                'phone' => '0200000702',
                'email' => 'esi@example.com',
                'status' => 'active',
                'join_date' => '2026-04-03',
                'record_admission_fee' => '1',
                'admission_payment_method' => 'cash',
                'admission_transaction_date' => '2026-04-03',
            ]);
            $this->fail('The member request should have failed when receipt creation failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('receipt failed', $exception->getMessage());
        }

        $this->assertSame(1, Member::count());
        $this->assertSame(0, Contribution::count());
        $this->assertSame(0, Receipt::count());
    }
}
