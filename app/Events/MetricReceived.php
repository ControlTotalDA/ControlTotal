<?php

namespace App\Events;

use App\Http\Resources\MetricResource;
use App\Models\Metric;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetricReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Metric $metric
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.'.$this->metric->tenant_id.'.machine.'.$this->metric->machine_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MetricReceived';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->metric->load('machine');

        return (new MetricResource($this->metric))->resolve();
    }
}
