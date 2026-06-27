<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tenant.{tenantId}.machine.{machineId}', function (User $user, string $tenantId, string $machineId): bool {
    return $user->tenant_id === $tenantId;
});
