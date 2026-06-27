<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Alert */
class AlertResource extends JsonResource
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
            'metric_id' => $this->metric_id,
            'type' => $this->type,
            'value' => $this->value,
            'threshold' => $this->threshold,
            'phase' => $this->phase,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'seen_at' => $this->seen_at?->toIso8601String(),
            'machine' => new MachineResource($this->whenLoaded('machine')),
            'metric' => new MetricResource($this->whenLoaded('metric')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
