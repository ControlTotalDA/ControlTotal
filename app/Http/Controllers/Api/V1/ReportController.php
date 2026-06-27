<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\Metric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Generate consumption summary report for all tenant machines.
     *
     * GET /api/v1/reports/summary?from=&to=
     */
    public function summary(Request $request): JsonResponse
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->utc()
            : now()->utc()->subDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->utc()
            : now()->utc();

        $machines = Machine::query()->where('active', true)->get();
        $machineReports = [];

        foreach ($machines as $machine) {
            $metricsQuery = Metric::query()
                ->where('machine_id', $machine->id)
                ->whereBetween('recorded_at', [$from, $to]);

            $aggregates = (clone $metricsQuery)->selectRaw('
                SUM(energy_kwh) as total_energy_kwh,
                MAX(power_real) as peak_power,
                MAX(voltage) as peak_voltage,
                MAX(current) as peak_current,
                AVG(power_real) as avg_power
            ')->first();

            $alertCount = Alert::query()
                ->where('machine_id', $machine->id)
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $machineReports[] = [
                'machine_id' => $machine->id,
                'machine_name' => $machine->name,
                'total_energy_kwh' => round((float) ($aggregates->total_energy_kwh ?? 0), 4),
                'peak_power' => round((float) ($aggregates->peak_power ?? 0), 2),
                'peak_voltage' => round((float) ($aggregates->peak_voltage ?? 0), 2),
                'peak_current' => round((float) ($aggregates->peak_current ?? 0), 2),
                'avg_power' => round((float) ($aggregates->avg_power ?? 0), 2),
                'alerts_count' => $alertCount,
            ];
        }

        $totalEnergy = collect($machineReports)->sum('total_energy_kwh');
        $totalAlerts = collect($machineReports)->sum('alerts_count');

        return ApiResponse::success([
            'period' => [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
            ],
            'totals' => [
                'energy_kwh' => round($totalEnergy, 4),
                'alerts_count' => $totalAlerts,
            ],
            'machines' => $machineReports,
        ]);
    }
}
