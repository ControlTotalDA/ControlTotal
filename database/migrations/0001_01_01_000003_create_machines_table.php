<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['laser', 'bending', 'cnc', 'press', 'other']);
            $table->enum('phases', ['single', 'split', 'three']);
            $table->decimal('max_voltage', 8, 2)->nullable();
            $table->decimal('min_voltage', 8, 2)->nullable();
            $table->decimal('max_current', 8, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
