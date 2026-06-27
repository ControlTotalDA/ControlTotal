<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Machine;
use App\Models\Metric;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['voltage_high', 'voltage_low', 'current_high', 'power_high', 'offline']);
        $value = fake()->randomFloat(2, 100, 300);
        $threshold = fake()->randomFloat(2, 200, 250);

        return [
            'tenant_id' => Tenant::factory(),
            'machine_id' => Machine::factory(),
            'metric_id' => Metric::factory(),
            'type' => $type,
            'value' => $value,
            'threshold' => $threshold,
            'phase' => fake()->randomElement(['L1', 'L2', 'L3']),
            'resolved_at' => null,
            'seen_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => ['resolved_at' => now()]);
    }

    public function seen(): static
    {
        return $this->state(fn () => ['seen_at' => now()]);
    }
}
