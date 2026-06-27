<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMetricRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Machine;
use App\Models\Tenant;
use App\Services\MetricService;
use Illuminate\Http\JsonResponse;

class MetricController extends Controller
{
    public function __construct(
        private readonly MetricService $metricService
    ) {}

    /**
     * Receive metric readings from n8n (authenticated via tenant API key).
     *
     * POST /api/v1/metrics
     */
    public function store(StoreMetricRequest $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $machineExists = Machine::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('id', $request->input('machine_id'))
            ->where('active', true)
            ->exists();

        if (! $machineExists) {
            return ApiResponse::error('Machine not found or inactive.', null, 404);
        }

        $this->metricService->queueIncomingMetrics($tenant, $request->validated());

        return ApiResponse::success(
            ['queued' => true],
            'Metrics queued for processing',
            null,
            202
        );
    }
}
