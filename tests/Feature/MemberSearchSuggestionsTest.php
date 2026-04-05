<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberSearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_members_search_returns_suggestions_for_single_letter(): void
    {
        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        Member::create([
            'membership_id' => 'CCM-101',
            'full_name' => 'Ama Serwaa',
            'phone' => '0200000101',
            'email' => 'ama@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        Member::create([
            'membership_id' => 'CCM-102',
            'full_name' => 'Kojo Mensah',
            'phone' => '0200000102',
            'email' => 'kojo@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($treasurer)->getJson('/members/suggestions?q=a');

        $response->assertOk();
        $response->assertJsonCount(2, 'suggestions');
        $response->assertJsonFragment([
            'full_name' => 'Ama Serwaa',
            'membership_id' => 'CCM-101',
        ]);
    }

    public function test_public_viewer_member_search_returns_suggestions_for_single_letter(): void
    {
        Member::create([
            'membership_id' => 'CCM-201',
            'full_name' => 'Akosua Arthur',
            'phone' => '0200000201',
            'email' => 'akosua@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        Member::create([
            'membership_id' => 'CCM-202',
            'full_name' => 'Kofi Osei',
            'phone' => '0200000202',
            'email' => null,
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $response = $this->getJson('/viewer/members/suggestions?q=a');

        $response->assertOk();
        $response->assertJsonFragment([
            'full_name' => 'Akosua Arthur',
            'membership_id' => 'CCM-201',
        ]);
        $response->assertJsonMissing([
            'full_name' => 'Kofi Osei',
            'membership_id' => 'CCM-202',
        ]);
    }
}
