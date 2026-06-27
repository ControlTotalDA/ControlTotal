<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\MachineFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    /** @use HasFactory<MachineFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'phases',
        'max_voltage',
        'min_voltage',
        'max_current',
        'active',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'max_voltage' => 'decimal:2',
            'min_voltage' => 'decimal:2',
            'max_current' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function latestMetric(): HasOne
    {
        return $this->hasOne(Metric::class)->latestOfMany('recorded_at');
    }
}
