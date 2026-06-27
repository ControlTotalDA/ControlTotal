<?php

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('Auth API', function () {
    it('logs in with valid credentials and returns token', function () {
        ['user' => $user] = createTenantWithAdmin('empresa-auth');

        $response = postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['token', 'user', 'tenant'],
            ]);
    });

    it('returns authenticated user on me endpoint', function () {
        ['user' => $user] = createTenantWithAdmin('empresa-me');

        $response = getJson('/api/v1/auth/me', authHeaders($user));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email);
    });

    it('logs out and revokes token', function () {
        ['user' => $user] = createTenantWithAdmin('empresa-logout');

        $headers = authHeaders($user);

        expect($user->tokens()->count())->toBe(1);

        postJson('/api/v1/auth/logout', [], $headers)->assertOk();

        expect($user->fresh()->tokens()->count())->toBe(0);

        app('auth')->forgetGuards();

        getJson('/api/v1/auth/me', $headers)->assertUnauthorized();
    });
});
