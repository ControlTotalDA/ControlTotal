<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->timestampTz('recorded_at');
            $table->enum('phase', ['L1', 'L2', 'L3']);
            $table->decimal('voltage', 8, 2);
            $table->decimal('current', 8, 2);
            $table->decimal('power_real', 10, 2);
            $table->decimal('power_apparent', 10, 2);
            $table->decimal('power_factor', 4, 3);
            $table->decimal('energy_kwh', 10, 4)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['tenant_id', 'machine_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
