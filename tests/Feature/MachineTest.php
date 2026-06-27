<?php

use App\Models\Machine;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Machine API', function () {
    it('lists only machines belonging to the authenticated tenant', function () {
        ['tenant' => $tenantA, 'user' => $userA] = createTenantWithAdmin('tenant-a');
        ['tenant' => $tenantB] = createTenantWithAdmin('tenant-b');

        Machine::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Machine A']);
        Machine::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Machine B']);

        $response = getJson('/api/v1/machines', authHeaders($userA));

        $response->assertOk();
        expect(collect($response->json('data')))->toHaveCount(1);
        expect($response->json('data.0.name'))->toBe('Machine A');
    });

    it('cannot access another tenant machine by id', function () {
        ['user' => $userA] = createTenantWithAdmin('tenant-a-access');
        ['tenant' => $tenantB] = createTenantWithAdmin('tenant-b-access');

        $foreignMachine = Machine::factory()->create(['tenant_id' => $tenantB->id]);

        getJson("/api/v1/machines/{$foreignMachine->id}", authHeaders($userA))
            ->assertNotFound();
    });

    it('creates updates and soft-deletes a machine', function () {
        ['user' => $user] = createTenantWithAdmin('tenant-crud');

        $create = postJson('/api/v1/machines', [
            'name' => 'Laser Test',
            'type' => 'laser',
            'phases' => 'three',
            'max_voltage' => 240,
            'min_voltage' => 210,
            'max_current' => 30,
            'location' => 'Planta 1',
        ], authHeaders($user));

        $create->assertCreated()
            ->assertJsonPath('data.name', 'Laser Test');

        $machineId = $create->json('data.id');

        putJson("/api/v1/machines/{$machineId}", [
            'name' => 'Laser Updated',
        ], authHeaders($user))
            ->assertOk()
            ->assertJsonPath('data.name', 'Laser Updated');

        deleteJson("/api/v1/machines/{$machineId}", [], authHeaders($user))
            ->assertOk()
            ->assertJsonPath('data.active', false);
    });
});
