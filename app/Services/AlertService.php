<?php

namespace App\Services;

use App\Events\AlertTriggered;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\Metric;

class AlertService
{
    /**
     * Evaluate metric readings against machine thresholds and create alerts when needed.
     *
     * @return array<int, Alert>
     */
    public function evaluateMetric(Machine $machine, Metric $metric): array
    {
        $alerts = [];

        if ($machine->max_voltage !== null && (float) $metric->voltage > (float) $machine->max_voltage) {
            $alert = $this->createAlertIfNotDuplicate(
                $machine,
                $metric,
                'voltage_high',
                (float) $metric->voltage,
                (float) $machine->max_voltage,
                $metric->phase
            );

            if ($alert) {
                $alerts[] = $alert;
            }
        }

        if ($machine->min_voltage !== null && (float) $metric->voltage < (float) $machine->min_voltage) {
            $alert = $this->createAlertIfNotDuplicate(
                $machine,
                $metric,
                'voltage_low',
                (float) $metric->voltage,
                (float) $machine->min_voltage,
                $metric->phase
            );

            if ($alert) {
                $alerts[] = $alert;
            }
        }

        if ($machine->max_current !== null && (float) $metric->current > (float) $machine->max_current) {
            $alert = $this->createAlertIfNotDuplicate(
                $machine,
                $metric,
                'current_high',
                (float) $metric->current,
                (float) $machine->max_current,
                $metric->phase
            );

            if ($alert) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    private function createAlertIfNotDuplicate(
        Machine $machine,
        Metric $metric,
        string $type,
        float $value,
        float $threshold,
        string $phase
    ): ?Alert {
        $exists = Alert::withoutGlobalScopes()
            ->where('tenant_id', $machine->tenant_id)
            ->where('machine_id', $machine->id)
            ->where('type', $type)
            ->where('phase', $phase)
            ->whereNull('resolved_at')
            ->exists();

        if ($exists) {
            return null;
        }

        $alert = Alert::withoutGlobalScopes()->create([
            'tenant_id' => $machine->tenant_id,
            'machine_id' => $machine->id,
            'metric_id' => $metric->id,
            'type' => $type,
            'value' => $value,
            'threshold' => $threshold,
            'phase' => $phase,
        ]);

        AlertTriggered::dispatch($alert);

        return $alert;
    }
}
