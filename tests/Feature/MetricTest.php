<?php

use App\Models\Alert;
use App\Models\Machine;
use App\Models\Metric;
use App\Jobs\ProcessMetricJob;
use Illuminate\Support\Facades\Queue;
use function Pest\Laravel\postJson;

describe('Metrics ingestion', function () {
    it('accepts metrics via tenant api key and queues processing', function () {
        Queue::fake();

        ['tenant' => $tenant] = createTenantWithAdmin('tenant-metrics');

        $machine = Machine::factory()->create([
            'tenant_id' => $tenant->id,
            'max_voltage' => 230,
            'min_voltage' => 210,
            'max_current' => 20,
        ]);

        $response = postJson('/api/v1/metrics', [
            'machine_id' => $machine->id,
            'recorded_at' => now()->utc()->toIso8601String(),
            'readings' => [
                [
                    'phase' => 'L1',
                    'voltage' => 225.5,
                    'current' => 10.2,
                    'power_real' => 2200,
                    'power_apparent' => 2300,
                    'power_factor' => 0.956,
                ],
            ],
        ], ['X-API-Key' => $tenant->api_key]);

        $response->assertAccepted()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.queued', true);

        Queue::assertPushed(ProcessMetricJob::class);
    });

    it('creates voltage_high alert when threshold is exceeded', function () {
        ['tenant' => $tenant] = createTenantWithAdmin('tenant-alerts');

        $machine = Machine::factory()->create([
            'tenant_id' => $tenant->id,
            'max_voltage' => 230,
            'min_voltage' => 210,
            'max_current' => 20,
        ]);

        postJson('/api/v1/metrics', [
            'machine_id' => $machine->id,
            'recorded_at' => now()->utc()->toIso8601String(),
            'readings' => [
                [
                    'phase' => 'L1',
                    'voltage' => 245.0,
                    'current' => 5.0,
                    'power_real' => 1000,
                    'power_apparent' => 1225,
                    'power_factor' => 0.816,
                ],
            ],
        ], ['X-API-Key' => $tenant->api_key])->assertAccepted();

        $alert = Alert::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('machine_id', $machine->id)
            ->where('type', 'voltage_high')
            ->first();

        expect($alert)->not->toBeNull();
        expect((float) $alert->value)->toBe(245.0);

        expect(Metric::withoutGlobalScopes()->where('machine_id', $machine->id)->count())->toBe(1);
    });

    it('rejects metrics without valid api key', function () {
        ['tenant' => $tenant] = createTenantWithAdmin('tenant-no-key');

        $machine = Machine::factory()->create(['tenant_id' => $tenant->id]);

        postJson('/api/v1/metrics', [
            'machine_id' => $machine->id,
            'recorded_at' => now()->utc()->toIso8601String(),
            'readings' => [
                [
                    'phase' => 'L1',
                    'voltage' => 220,
                    'current' => 5,
                    'power_real' => 1000,
                    'power_apparent' => 1100,
                    'power_factor' => 0.9,
                ],
            ],
        ])->assertUnauthorized();
    });
});
