<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    protected $model = Machine::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['laser', 'bending', 'cnc', 'press', 'other']),
            'phases' => fake()->randomElement(['single', 'split', 'three']),
            'max_voltage' => fake()->randomFloat(2, 220, 240),
            'min_voltage' => fake()->randomFloat(2, 200, 210),
            'max_current' => fake()->randomFloat(2, 10, 50),
            'active' => true,
            'location' => fake()->optional()->randomElement(['Planta A', 'Planta B', 'Área CNC']),
        ];
    }
}
