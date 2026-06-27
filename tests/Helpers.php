<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createTenantWithAdmin(string $slug = 'test-tenant'): array
{
    $tenant = Tenant::factory()->create(['slug' => $slug]);

    $user = User::factory()->admin()->create([
        'tenant_id' => $tenant->id,
        'email' => "admin@{$slug}.com",
        'password' => Hash::make('password'),
    ]);

    return compact('tenant', 'user');
}

function authHeaders(User $user): array
{
    $token = $user->createToken('test')->plainTextToken;

    return ['Authorization' => "Bearer {$token}"];
}
