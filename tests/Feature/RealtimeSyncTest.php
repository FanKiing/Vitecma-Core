<?php

namespace Tests\Feature;

use App\Events\DashboardChanged;
use App\Events\InspectionStatusUpdated;
use App\Models\Inspection;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RealtimeSyncTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function technician(): Technician
    {
        return Technician::create(['name' => 'Test Technician', 'identifier' => 'tech-sync', 'password' => Hash::make('test-only-password'), 'is_active' => true]);
    }

    public function test_snapshot_requires_authentication_and_is_rendered_for_the_viewer_role(): void
    {
        $this->getJson('/inspections/snapshot')->assertUnauthorized();
        Inspection::factory()->create(['status' => 'libre', 'owner_name' => 'Test Owner']);
        $tech = $this->technician();
        $screen = User::factory()->create(['role' => 'screen']);
        $response = $this->actingAs($screen)->getJson('/inspections/snapshot')->assertOk()->assertJsonPath('totalCount', 1)->assertJsonPath('technicians', []);
        $this->assertStringContainsString('Test Owner', $response->json('html'));
        $this->assertStringNotContainsString('row-checkbox', $response->json('html'));
        $this->assertStringNotContainsString('action-btn', $response->json('html'));
        $this->assertStringNotContainsString($tech->password, $response->getContent());
        $response = $this->actingAs($this->administrator())->getJson('/inspections/snapshot')->assertOk();
        $this->assertStringContainsString('row-checkbox', $response->json('html'));
        $this->assertStringContainsString('action-btn', $response->json('html'));
        $this->assertSame($tech->identifier, $response->json('technicians.0.identifier'));
        $this->assertArrayNotHasKey('password', $response->json('technicians.0'));
    }

    public function test_snapshot_contains_all_edited_fields_and_active_timer_for_screen(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        $tech = $this->technician();
        $inspection = Inspection::factory()->create(['status' => 'libre', 'started_at' => null]);
        $this->actingAs($this->administrator())->patchJson('/inspections/'.$inspection->id, [
            'plate_number' => 'EDITED-123', 'owner_name' => 'Updated Owner', 'category' => 'PL',
        ])->assertOk();
        $this->patchJson('/inspections/'.$inspection->id.'/status', [
            'status' => 'en_cours', 'technician_identifier' => $tech->identifier,
            'technician_password' => 'test-only-password', 'lane' => 'PL1',
        ])->assertOk();
        $response = $this->actingAs(User::factory()->create(['role' => 'screen']))->getJson('/inspections/snapshot')->assertOk();
        foreach (['EDITED-123', 'Updated Owner', 'Test Technician', 'PL1', 'data-active="true"', 'data-duration="30"'] as $text) {
            $this->assertStringContainsString($text, $response->json('html'));
        }
        $this->assertIsInt($response->json('serverTime'));
    }

    public function test_delete_event_is_dispatched_after_deletion_with_the_correct_count(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        $inspection = Inspection::factory()->create(['status' => 'libre']);
        $this->actingAs($this->administrator())->deleteJson('/inspections/'.$inspection->id)->assertOk()->assertJsonPath('totalCount', 0);
        Event::assertDispatched(InspectionStatusUpdated::class, fn ($event) => $event->actionType === 'delete' && $event->totalCount === 0);
        $this->getJson('/inspections/snapshot')->assertJsonPath('totalCount', 0);
    }

    public function test_bulk_delete_sends_one_final_event_and_keeps_non_free_rows(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        $rows = Inspection::factory()->count(3)->create(['status' => 'libre']);
        $active = Inspection::factory()->create(['status' => 'en_cours']);
        $this->actingAs($this->administrator())->deleteJson('/inspections/bulk-delete', ['ids' => [...$rows->modelKeys(), $active->id]])->assertOk()->assertJsonPath('totalCount', 1)->assertJsonPath('count', 3);
        Event::assertDispatchedTimes(InspectionStatusUpdated::class, 1);
        Event::assertDispatched(InspectionStatusUpdated::class, fn ($event) => $event->actionType === 'bulk_delete' && $event->totalCount === 1);
        $this->assertNotNull($active->fresh());
    }

    public function test_repeat_start_and_reverting_archive_do_not_reset_the_original_start_time(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        $tech = $this->technician();
        $started = now()->subMinutes(7)->startOfSecond();
        $row = Inspection::factory()->create(['status' => 'en_cours', 'started_at' => $started, 'technician_id' => $tech->id, 'technician_name' => $tech->name, 'lane' => 'VL2']);
        $this->actingAs($this->administrator())->patchJson('/inspections/'.$row->id.'/status', [
            'status' => 'en_cours', 'technician_identifier' => $tech->identifier, 'technician_password' => 'test-only-password', 'lane' => 'VL2',
        ])->assertOk();
        $this->assertTrue($row->fresh()->started_at->equalTo($started));
        $this->patchJson('/inspections/'.$row->id.'/status', ['status' => 'valider', 'result' => 'favorable'])->assertOk();
        $this->patchJson('/inspections/'.$row->id.'/status', ['status' => 'imprimer'])->assertOk();
        $this->getJson('/inspections/snapshot')->assertJsonPath('totalCount', 0);
        $this->postJson('/inspections/'.$row->id.'/revert')->assertOk();
        $this->postJson('/inspections/'.$row->id.'/revert')->assertOk();
        $this->assertSame('en_cours', $row->fresh()->status);
        $this->assertTrue($row->fresh()->started_at->equalTo($started));
        $this->postJson('/inspections/'.$row->id.'/revert')->assertOk();
        $this->assertNull($row->fresh()->started_at);
    }

    public function test_restoring_an_active_row_preserves_its_state_and_timer(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        $row = Inspection::factory()->create(['status' => 'en_cours', 'started_at' => now()->subMinutes(4)]);
        $row->delete();
        $this->actingAs($this->administrator())->postJson('/inspections/'.$row->id.'/restore')->assertOk();
        $response = $this->getJson('/inspections/snapshot')->assertJsonPath('totalCount', 1);
        $this->assertStringContainsString('data-active="true"', $response->json('html'));
        $this->assertSame('en_cours', $row->fresh()->status);
    }

    public function test_snapshot_clamps_removed_pages_and_has_deterministic_order(): void
    {
        $rows = Inspection::factory()->count(54)->create(['status' => 'libre', 'created_at' => now()]);
        $this->actingAs(User::factory()->create(['role' => 'screen']));
        $response = $this->getJson('/inspections/snapshot?page=2')->assertOk()->assertJsonPath('page', 2)->assertJsonPath('totalCount', 54);
        $this->assertStringContainsString('id="row-'.$rows->first()->id.'"', $response->json('html'));
        $rows->last()->delete();
        $this->getJson('/inspections/snapshot?page=2')->assertOk()->assertJsonPath('page', 1)->assertJsonPath('totalCount', 53);
    }

    public function test_technician_mutations_invalidate_other_dashboards_without_broadcasting_credentials(): void
    {
        Event::fake([DashboardChanged::class]);
        $tech = $this->technician();
        $this->actingAs($this->administrator())->postJson('/technicians/'.$tech->id.'/toggle')->assertOk();
        Event::assertDispatchedTimes(DashboardChanged::class, 1);
        $this->getJson('/inspections/snapshot')->assertJsonPath('technicians', []);
        $this->assertSame([], (new DashboardChanged)->broadcastWith());
    }

    public function test_snapshot_recovers_current_maintenance_state_and_cleared_message(): void
    {
        $this->actingAs($this->administrator())->postJson('/maintenance/toggle', ['enabled' => true, 'message' => 'Please wait'])->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'screen']))->getJson('/inspections/snapshot')->assertJsonPath('maintenance.enabled', true)->assertJsonPath('maintenance.message', 'Please wait');
        $this->actingAs($this->administrator())->postJson('/maintenance/toggle', ['enabled' => false])->assertOk();
        $this->getJson('/inspections/snapshot')->assertJsonPath('maintenance.enabled', false)->assertJsonPath('maintenance.message', null);
    }

    public function test_screen_cannot_request_archive_or_trash_snapshots(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'screen']));
        $this->getJson('/inspections/snapshot?scope=archive')->assertForbidden();
        $this->getJson('/inspections/snapshot?scope=trash')->assertForbidden();
    }

    public function test_archive_and_trash_snapshots_reconcile_their_own_rows_and_counts(): void
    {
        Event::fake([InspectionStatusUpdated::class, DashboardChanged::class]);
        $archive = Inspection::factory()->create(['status' => 'imprimer', 'plate_number' => 'ARCHIVE-ONLY']);
        $trash = Inspection::factory()->create(['status' => 'libre', 'plate_number' => 'TRASH-ONLY']);
        $trash->delete();
        $this->actingAs($this->administrator());
        $response = $this->getJson('/inspections/snapshot?scope=archive')->assertOk()->assertJsonPath('totalCount', 1);
        $this->assertStringContainsString('ARCHIVE-ONLY', $response->json('html'));
        $this->assertStringNotContainsString('TRASH-ONLY', $response->json('html'));
        $response = $this->getJson('/inspections/snapshot?scope=trash')->assertOk()->assertJsonPath('totalCount', 1);
        $this->assertStringContainsString('TRASH-ONLY', $response->json('html'));
        $this->deleteJson('/inspections/'.$trash->id.'/permanent-delete')->assertOk();
        $this->getJson('/inspections/snapshot?scope=trash')->assertJsonPath('totalCount', 0);
        Event::assertDispatchedTimes(DashboardChanged::class, 1);
        $this->assertNotNull($archive->fresh());
    }

    public function test_empty_trash_broadcasts_a_final_invalidation(): void
    {
        Event::fake([DashboardChanged::class]);
        $row = Inspection::factory()->create();
        $row->delete();
        $this->actingAs($this->administrator())->deleteJson('/inspections/trash/empty')->assertOk();
        $this->assertDatabaseCount('inspections', 0);
        Event::assertDispatchedTimes(DashboardChanged::class, 1);
    }

    public function test_a_broadcast_outage_does_not_turn_a_saved_mutation_into_a_failed_request(): void
    {
        $driver = \Mockery::mock(Broadcaster::class);
        $driver->shouldReceive('broadcast')->once()->andThrow(new BroadcastException('Test transport unavailable'));
        Broadcast::extend('unavailable', fn () => $driver);
        config(['broadcasting.default' => 'unavailable', 'broadcasting.connections.unavailable' => ['driver' => 'unavailable']]);
        $this->actingAs($this->administrator())->postJson('/inspections', [
            'plate_number' => 'OUTAGE-123', 'owner_name' => 'Saved Owner', 'category' => 'VL',
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('inspections', ['plate_number' => 'OUTAGE-123']);
        $response = $this->getJson('/inspections/snapshot')->assertOk()->assertJsonPath('totalCount', 1);
        $this->assertStringContainsString('Saved Owner', $response->json('html'));
    }
}
