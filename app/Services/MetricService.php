<?php

namespace App\Services;

use App\Events\MetricReceived;
use App\Jobs\ProcessMetricJob;
use App\Models\Machine;
use App\Models\Metric;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MetricService
{
    public function __construct(
        private readonly AlertService $alertService
    ) {}

    /**
     * Queue incoming n8n metric payload for async processing.
     *
     * @param  array<string, mixed>  $payload
     */
    public function queueIncomingMetrics(Tenant $tenant, array $payload): void
    {
        ProcessMetricJob::dispatch(
            $tenant->id,
            $payload['machine_id'],
            Carbon::parse($payload['recorded_at'])->utc(),
            $payload['readings']
        );
    }

    /**
     * Persist readings, evaluate alerts, and broadcast the latest metric.
     *
     * @param  array<int, array<string, mixed>>  $readings
     * @return array<int, Metric>
     */
    public function processReadings(string $tenantId, string $machineId, Carbon $recordedAt, array $readings): array
    {
        $machine = Machine::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('id', $machineId)
            ->where('active', true)
            ->firstOrFail();

        $metrics = [];

        DB::transaction(function () use ($machine, $recordedAt, $readings, &$metrics): void {
            foreach ($readings as $reading) {
                $metric = Metric::withoutGlobalScopes()->create([
                    'tenant_id' => $machine->tenant_id,
                    'machine_id' => $machine->id,
                    'recorded_at' => $recordedAt,
                    'phase' => $reading['phase'],
                    'voltage' => $reading['voltage'],
                    'current' => $reading['current'],
                    'power_real' => $reading['power_real'],
                    'power_apparent' => $reading['power_apparent'],
                    'power_factor' => $reading['power_factor'],
                    'energy_kwh' => $reading['energy_kwh'] ?? null,
                ]);

                $this->alertService->evaluateMetric($machine, $metric);
                $metrics[] = $metric;
            }
        });

        if ($metrics !== []) {
            $latest = collect($metrics)->sortByDesc('recorded_at')->first();
            MetricReceived::dispatch($latest);
        }

        return $metrics;
    }
}
