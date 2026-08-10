<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagedFileStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_constitution_upload_uses_the_configured_managed_disk(): void
    {
        Storage::fake('club-files');
        Storage::fake('public');
        config(['ccm.files_disk' => 'club-files']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'constitution_file' => UploadedFile::fake()->create('constitution.pdf', 100, 'application/pdf'),
            'club_start_date' => '2026-01-01',
            'monthly_dues_amount' => '50',
            'birthday_message_template' => 'Happy birthday :name',
        ]);

        $response->assertRedirect(route('admin.settings'));

        $path = Setting::getValue('constitution_path');

        $this->assertNotNull($path);
        Storage::disk('club-files')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_receipt_pdfs_use_the_configured_managed_disk(): void
    {
        Storage::fake('club-files');
        Storage::fake('public');
        config(['ccm.files_disk' => 'club-files']);

        $treasurer = User::factory()->create([
            'role' => 'treasurer',
            'is_active' => true,
        ]);

        $member = Member::create([
            'membership_id' => 'CCM-950',
            'full_name' => 'Nana Asante',
            'phone' => '0200000950',
            'email' => 'nana@example.com',
            'status' => 'active',
            'join_date' => '2026-01-01',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'type' => 'Monthly Dues',
            'description' => 'Monthly dues for May 2026',
            'amount' => 50,
            'payment_method' => 'cash',
            'transaction_date' => '2026-05-01',
            'recorded_by' => $treasurer->id,
        ]);

        $contribution->load('member');

        $receipt = app(ReceiptService::class)->createForContribution($contribution, $treasurer);

        Storage::disk('club-files')->assertExists($receipt->pdf_path);
        Storage::disk('public')->assertMissing($receipt->pdf_path);
    }
}
