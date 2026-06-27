<?php

namespace App\Jobs;

use App\Services\MetricService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessMetricJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $readings
     */
    public function __construct(
        public string $tenantId,
        public string $machineId,
        public Carbon $recordedAt,
        public array $readings
    ) {}

    public function handle(MetricService $metricService): void
    {
        $metricService->processReadings(
            $this->tenantId,
            $this->machineId,
            $this->recordedAt,
            $this->readings
        );
    }
}
