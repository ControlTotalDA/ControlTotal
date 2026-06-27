<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Machine */
class MachineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'type' => $this->type,
            'phases' => $this->phases,
            'max_voltage' => $this->max_voltage,
            'min_voltage' => $this->min_voltage,
            'max_current' => $this->max_current,
            'active' => $this->active,
            'location' => $this->location,
            'latest_metric' => new MetricResource($this->whenLoaded('latestMetric')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
