<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Metric */
class MetricResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'machine_id' => $this->machine_id,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
            'phase' => $this->phase,
            'voltage' => $this->voltage,
            'current' => $this->current,
            'power_real' => $this->power_real,
            'power_apparent' => $this->power_apparent,
            'power_factor' => $this->power_factor,
            'energy_kwh' => $this->energy_kwh,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
