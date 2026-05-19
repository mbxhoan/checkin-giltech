<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventDataTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_datatable_ajax_handles_events_without_run_dates(): void
    {
        $admin = $this->admin([
            'is_admin' => true,
        ]);

        $company = Company::query()->create([
            'name' => 'Giltech QA',
            'status' => Company::STATUS_ACTIVE,
        ]);

        Event::query()->create([
            'company_id' => $company->id,
            'code' => 'EVT-NULL-DATE',
            'name' => 'Event Without Dates',
            'status' => Event::STATUS_ACTIVE,
            'from_date' => null,
            'to_date' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]), [
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertSee('EVT-NULL-DATE')
            ->assertSee('Chưa cập nhật');
    }
}
