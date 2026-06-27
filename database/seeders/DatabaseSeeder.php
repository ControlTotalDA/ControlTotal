<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Machine;
use App\Models\Metric;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Empresa A',
                'slug' => 'empresa-a',
                'admin_email' => 'admin@empresa-a.com',
            ],
            [
                'name' => 'Empresa B',
                'slug' => 'empresa-b',
                'admin_email' => 'admin@empresa-b.com',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::factory()->create([
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug'],
                'plan' => 'pro',
            ]);

            User::factory()->admin()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin '.$tenantData['name'],
                'email' => $tenantData['admin_email'],
                'password' => Hash::make('password'),
            ]);

            $machines = collect([
                ['name' => 'Laser Trumpf #1', 'type' => 'laser', 'phases' => 'three'],
                ['name' => 'Plegadora Amada #2', 'type' => 'bending', 'phases' => 'split'],
                ['name' => 'CNC Haas #3', 'type' => 'cnc', 'phases' => 'single'],
            ])->map(fn (array $data) => Machine::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'phases' => $data['phases'],
                'max_voltage' => 240.00,
                'min_voltage' => 210.00,
                'max_current' => 32.00,
            ]));

            foreach ($machines as $machine) {
                Metric::factory()
                    ->count(100)
                    ->create([
                        'tenant_id' => $tenant->id,
                        'machine_id' => $machine->id,
                        'recorded_at' => fn () => now()->utc()->subMinutes(fake()->numberBetween(1, 1440)),
                    ]);
            }

            Alert::factory()
                ->count(5)
                ->create([
                    'tenant_id' => $tenant->id,
                    'machine_id' => fn () => $machines->random()->id,
                    'metric_id' => fn (array $attributes) => Metric::withoutGlobalScopes()
                        ->where('machine_id', $attributes['machine_id'])
                        ->inRandomOrder()
                        ->value('id'),
                ]);
        }
    }
}
