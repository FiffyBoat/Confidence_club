<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuesArrearsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dues_index_shows_only_members_with_outstanding_dues_and_amounts_owed(): void
    {
        Setting::create(['key' => 'club_start_date', 'value' => '2026-01-01']);
        Setting::create(['key' => 'monthly_dues_amount', 'value' => '50']);

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $alice = Member::create([
            'membership_id' => 'CCM-001',
            'full_name' => 'Alice Mensah',
            'phone' => '0200000001',
            'email' => 'alice@example.com',
            'status' => 'active',
            'join_date' => '2026-01-05',
        ]);

        $bob = Member::create([
            'membership_id' => 'CCM-002',
            'full_name' => 'Bob Owusu',
            'phone' => '0200000002',
            'email' => 'bob@example.com',
            'status' => 'active',
            'join_date' => '2026-01-05',
        ]);

        $carol = Member::create([
            'membership_id' => 'CCM-003',
            'full_name' => 'Carol Aidoo',
            'phone' => '0200000003',
            'email' => 'carol@example.com',
            'status' => 'active',
            'join_date' => '2026-04-02',
        ]);

        $dave = Member::create([
            'membership_id' => 'CCM-004',
            'full_name' => 'Dave Asante',
            'phone' => '0200000004',
            'email' => 'dave@example.com',
            'status' => 'active',
            'join_date' => '2026-01-05',
        ]);

        Contribution::create([
            'member_id' => $bob->id,
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for January 2026',
            'amount' => 50,
            'payment_method' => 'cash',
            'transaction_date' => '2026-01-01',
            'recorded_by' => $treasurer->id,
        ]);

        Contribution::create([
            'member_id' => $dave->id,
            'type' => 'Monthly Dues',
            'description' => 'Advance dues payment',
            'amount' => 200,
            'payment_method' => 'cash',
            'transaction_date' => '2026-01-01',
            'recorded_by' => $treasurer->id,
        ]);

        $response = $this->actingAs($treasurer)->get('/dues?year=2026&as_of=4');

        $response->assertOk();
        $response->assertSee('Members With Outstanding Dues');
        $response->assertSee('No Dues Paid Yet');

        $outstandingRows = collect($response->viewData('outstandingRows'));

        $this->assertCount(3, $outstandingRows);

        $aliceRow = $outstandingRows->first(fn (array $row) => $row['member']->is($alice));
        $this->assertNotNull($aliceRow);
        $this->assertSame('none', $aliceRow['payment_status']);
        $this->assertEquals(200.0, $aliceRow['balance_end']);
        $this->assertSame(
            ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026'],
            array_column($aliceRow['arrears_months'], 'label')
        );

        $bobRow = $outstandingRows->first(fn (array $row) => $row['member']->is($bob));
        $this->assertNotNull($bobRow);
        $this->assertSame('partial', $bobRow['payment_status']);
        $this->assertEquals(150.0, $bobRow['balance_end']);
        $this->assertSame(
            ['Feb 2026', 'Mar 2026', 'Apr 2026'],
            array_column($bobRow['arrears_months'], 'label')
        );

        $carolRow = $outstandingRows->first(fn (array $row) => $row['member']->is($carol));
        $this->assertNotNull($carolRow);
        $this->assertSame('none', $carolRow['payment_status']);
        $this->assertEquals(50.0, $carolRow['balance_end']);
        $this->assertSame(['Apr 2026'], array_column($carolRow['arrears_months'], 'label'));

        $this->assertNull($outstandingRows->first(fn (array $row) => $row['member']->is($dave)));
    }

    public function test_dues_arrears_csv_download_contains_only_members_who_owe(): void
    {
        Setting::create(['key' => 'club_start_date', 'value' => '2026-01-01']);
        Setting::create(['key' => 'monthly_dues_amount', 'value' => '50']);

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $owingMember = Member::create([
            'membership_id' => 'CCM-010',
            'full_name' => 'Esi Boateng',
            'phone' => '0200000010',
            'email' => 'esi@example.com',
            'status' => 'active',
            'join_date' => '2026-01-05',
        ]);

        $clearedMember = Member::create([
            'membership_id' => 'CCM-011',
            'full_name' => 'Kojo Addo',
            'phone' => '0200000011',
            'email' => 'kojo@example.com',
            'status' => 'active',
            'join_date' => '2026-01-05',
        ]);

        Contribution::create([
            'member_id' => $clearedMember->id,
            'type' => 'Monthly Dues',
            'description' => 'Advance dues payment',
            'amount' => 200,
            'payment_method' => 'cash',
            'transaction_date' => '2026-01-01',
            'recorded_by' => $treasurer->id,
        ]);

        $response = $this->actingAs($treasurer)->get('/dues/arrears.csv?year=2026&as_of=4');

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', (string) $response->headers->get('Content-Type'));
        $response->assertSee('member_id,member_name,status,months_due,paid_to_date,amount_owed,unpaid_months', false);
        $response->assertSee('CCM-010', false);
        $response->assertSee('Esi Boateng', false);
        $response->assertSee('200.00', false);
        $response->assertDontSee('CCM-011', false);
    }
}
