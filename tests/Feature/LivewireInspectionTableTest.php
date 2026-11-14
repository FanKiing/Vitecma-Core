<?php

namespace Tests\Feature;

use App\Events\InspectionStatusUpdated;
use App\Livewire\InspectionTable;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireInspectionTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_screen_receives_reverb_refreshes_without_administrator_controls(): void
    {
        $row = Inspection::factory()->create(['status' => 'libre', 'owner_name' => 'Before']);
        $component = Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class)->assertSee('Before')->assertDontSee('row-checkbox', false);
        $row->update(['owner_name' => 'After']);
        $component->dispatch('echo-private:inspections-channel,.inspection.changed')->assertSee('After')->assertDontSee('row-checkbox', false);
    }

    public function test_screen_cannot_call_mutation_actions_directly(): void
    {
        $row = Inspection::factory()->create();
        Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class)->call('command', 'delete', $row->id)->assertForbidden();
        $this->assertNotNull($row->fresh());
    }

    public function test_administrator_commands_validate_persist_and_broadcast(): void
    {
        Event::fake([InspectionStatusUpdated::class]);
        Livewire::actingAs(User::factory()->create(['role' => 'admin']))->test(InspectionTable::class)
            ->call('command', 'create', null, ['plate_number' => 'WIRE-123', 'owner_name' => 'Livewire Owner', 'category' => 'VL'])
            ->assertSee('Livewire Owner');
        $this->assertDatabaseHas('inspections', ['plate_number' => 'WIRE-123', 'owner_name' => 'Livewire Owner']);
        Event::assertDispatchedTimes(InspectionStatusUpdated::class, 1);
    }

    public function test_invalid_commands_do_not_write_data(): void
    {
        Livewire::actingAs(User::factory()->create(['role' => 'admin']))->test(InspectionTable::class)
            ->call('command', 'create', null, ['plate_number' => '', 'owner_name' => '', 'category' => 'bad'])
            ->assertReturned(fn ($result) => $result['success'] === false && isset($result['errors']['category']));
        $this->assertDatabaseCount('inspections', 0);
    }

    public function test_scope_cannot_be_changed_by_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class)->set('scope', 'trash');
    }

    public function test_screen_cannot_mount_an_administrator_scope(): void
    {
        Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class, ['scope' => 'archive'])->assertForbidden();
    }

    public function test_refresh_preserves_timer_anchor_and_restored_state(): void
    {
        $row = Inspection::factory()->create(['status' => 'en_cours', 'started_at' => now()->subMinutes(4)]);
        $start = $row->started_at->toIso8601String();
        Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class)
            ->assertSee($start, false)->call('refreshState')->assertSee($start, false)->assertSee('data-active="true"', false);
    }

    public function test_pagination_is_owned_by_livewire_and_recovers_a_deleted_last_page(): void
    {
        $rows = Inspection::factory()->count(54)->sequence(fn ($sequence) => ['plate_number' => 'PAGE-'.$sequence->index])->create(['status' => 'libre', 'created_at' => now()]);
        $component = Livewire::actingAs(User::factory()->create(['role' => 'screen']))->test(InspectionTable::class)
            ->assertSee('PAGE-53')->assertDontSee('PAGE-0')->call('setPage', 2)->assertSee('PAGE-0');
        $rows->last()->delete();
        $component->call('refreshState')->assertSet('paginators.page', 1)->assertSee('PAGE-0');
    }
}
