<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignUuid('metric_id')->nullable()->constrained('metrics')->nullOnDelete();
            $table->enum('type', ['voltage_high', 'voltage_low', 'current_high', 'power_high', 'offline']);
            $table->decimal('value', 10, 2);
            $table->decimal('threshold', 10, 2);
            $table->enum('phase', ['L1', 'L2', 'L3'])->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampTz('seen_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'machine_id', 'type', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
