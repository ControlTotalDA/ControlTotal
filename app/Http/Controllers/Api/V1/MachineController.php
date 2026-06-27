<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Http\Resources\MachineResource;
use App\Http\Resources\MetricResource;
use App\Http\Responses\ApiResponse;
use App\Models\Machine;
use App\Models\Metric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MachineController extends Controller
{
    /**
     * List machines with optional filters.
     *
     * GET /api/v1/machines?type=laser&active=true
     */
    public function index(Request $request): JsonResponse
    {
        $query = Machine::query()->with('latestMetric');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->has('active')) {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $machines = $query->orderBy('name')->paginate(20);

        return ApiResponse::success(MachineResource::collection($machines));
    }

    /**
     * Create a new machine for the current tenant.
     *
     * POST /api/v1/machines
     */
    public function store(StoreMachineRequest $request): JsonResponse
    {
        $machine = Machine::create($request->validated());
        $machine->load('latestMetric');

        return ApiResponse::success(new MachineResource($machine), 'Machine created', null, 201);
    }

    /**
     * Show machine detail with latest metric.
     *
     * GET /api/v1/machines/{id}
     */
    public function show(Machine $machine): JsonResponse
    {
        $machine->load('latestMetric');

        return ApiResponse::success(new MachineResource($machine));
    }

    /**
     * Update machine configuration.
     *
     * PUT /api/v1/machines/{id}
     */
    public function update(UpdateMachineRequest $request, Machine $machine): JsonResponse
    {
        $machine->update($request->validated());
        $machine->load('latestMetric');

        return ApiResponse::success(new MachineResource($machine), 'Machine updated');
    }

    /**
     * Soft-delete machine by setting active=false.
     *
     * DELETE /api/v1/machines/{id}
     */
    public function destroy(Machine $machine): JsonResponse
    {
        $machine->update(['active' => false]);

        return ApiResponse::success(new MachineResource($machine->fresh('latestMetric')), 'Machine deactivated');
    }

    /**
     * Paginated metric history for a machine.
     *
     * GET /api/v1/machines/{id}/metrics?from=&to=&phase=L1
     */
    public function metrics(Request $request, Machine $machine): JsonResponse
    {
        $query = Metric::query()
            ->where('machine_id', $machine->id)
            ->orderByDesc('recorded_at');

        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', Carbon::parse($request->input('from'))->utc());
        }

        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', Carbon::parse($request->input('to'))->utc());
        }

        if ($request->filled('phase')) {
            $query->where('phase', $request->string('phase'));
        }

        $metrics = $query->paginate(50);

        return ApiResponse::success(MetricResource::collection($metrics));
    }

    /**
     * Summary statistics for a machine over a time period.
     *
     * GET /api/v1/machines/{id}/stats?from=&to=
     */
    public function stats(Request $request, Machine $machine): JsonResponse
    {
        $query = Metric::query()->where('machine_id', $machine->id);

        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', Carbon::parse($request->input('from'))->utc());
        }

        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', Carbon::parse($request->input('to'))->utc());
        }

        $stats = $query->selectRaw('
            AVG(voltage) as avg_voltage,
            AVG(current) as avg_current,
            AVG(power_real) as avg_power_real,
            MAX(voltage) as max_voltage,
            MAX(current) as max_current,
            MAX(power_real) as max_power_real,
            SUM(energy_kwh) as total_energy_kwh,
            COUNT(*) as readings_count
        ')->first();

        return ApiResponse::success([
            'machine_id' => $machine->id,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'avg_voltage' => round((float) ($stats->avg_voltage ?? 0), 2),
            'avg_current' => round((float) ($stats->avg_current ?? 0), 2),
            'avg_power_real' => round((float) ($stats->avg_power_real ?? 0), 2),
            'max_voltage' => round((float) ($stats->max_voltage ?? 0), 2),
            'max_current' => round((float) ($stats->max_current ?? 0), 2),
            'max_power_real' => round((float) ($stats->max_power_real ?? 0), 2),
            'total_energy_kwh' => round((float) ($stats->total_energy_kwh ?? 0), 4),
            'readings_count' => (int) ($stats->readings_count ?? 0),
        ]);
    }
}
