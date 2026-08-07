<?php

namespace Tests\Feature\Cabinet;

use App\Enums\CabinetStatus;
use App\Mail\CabinetActivatedMail;
use App\Models\Cabinet;
use App\Models\License;
use App\Models\User;
use App\Services\CabinetFulfillmentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CabinetActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_activation_issues_a_perpetual_license_and_notifies_owner(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $cabinet = Cabinet::query()->create([
            'name' => 'Cabinet Pending',
            'status' => CabinetStatus::PENDING,
            'owner_user_id' => $owner->getKey(),
        ]);
        $owner->forceFill(['cabinet_id' => $cabinet->getKey()])->save();

        $service = app(CabinetFulfillmentService::class);
        $service->activate($cabinet->fresh());

        $cabinet->refresh();
        $this->assertSame(CabinetStatus::ACTIVE, $cabinet->status);
        $this->assertNotNull($cabinet->activated_at);
        $this->assertNotNull($cabinet->license_id);

        $license = License::query()->findOrFail($cabinet->license_id);
        $this->assertSame('active', $license->status);
        $this->assertNull($license->expires_at, 'A hosted license is perpetual.');

        // CabinetActivatedMail implements ShouldQueue, so under Mail::fake()
        // it is recorded as queued rather than sent.
        Mail::assertQueued(CabinetActivatedMail::class);

        $this->assertDatabaseHas('audit_logs', ['action' => 'cabinet.activated']);
    }

    public function test_suspend_and_reactivate_transitions(): void
    {
        $cabinet = Cabinet::query()->create([
            'name' => 'Cabinet',
            'status' => CabinetStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $service = app(CabinetFulfillmentService::class);

        $service->suspend($cabinet);
        $this->assertSame(CabinetStatus::SUSPENDED, $cabinet->fresh()->status);

        $service->reactivate($cabinet->fresh());
        $this->assertSame(CabinetStatus::ACTIVE, $cabinet->fresh()->status);
    }

    public function test_only_platform_admins_reach_the_filament_cabinets_page(): void
    {
        $regular = User::factory()->create(['is_platform_admin' => false]);
        $this->actingAs($regular)
            ->get('/admin/cabinets')
            ->assertForbidden();

        $platform = User::factory()->create(['is_platform_admin' => true]);
        $this->actingAs($platform)
            ->get('/admin/cabinets')
            ->assertSuccessful();
    }
}
