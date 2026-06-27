<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Metric;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Metric>
 */
class MetricFactory extends Factory
{
    protected $model = Metric::class;

    public function definition(): array
    {
        $voltage = fake()->randomFloat(2, 210, 235);
        $current = fake()->randomFloat(2, 1, 30);
        $powerFactor = fake()->randomFloat(3, 0.85, 0.99);

        return [
            'tenant_id' => Tenant::factory(),
            'machine_id' => Machine::factory(),
            'recorded_at' => fake()->dateTimeBetween('-24 hours', 'now'),
            'phase' => fake()->randomElement(['L1', 'L2', 'L3']),
            'voltage' => $voltage,
            'current' => $current,
            'power_real' => round($voltage * $current * $powerFactor, 2),
            'power_apparent' => round($voltage * $current, 2),
            'power_factor' => $powerFactor,
            'energy_kwh' => fake()->optional()->randomFloat(4, 0.1, 5),
        ];
    }
}
