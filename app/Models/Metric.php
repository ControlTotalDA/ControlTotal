<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\MetricFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metric extends Model
{
    /** @use HasFactory<MetricFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'machine_id',
        'recorded_at',
        'phase',
        'voltage',
        'current',
        'power_real',
        'power_apparent',
        'power_factor',
        'energy_kwh',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'voltage' => 'decimal:2',
            'current' => 'decimal:2',
            'power_real' => 'decimal:2',
            'power_apparent' => 'decimal:2',
            'power_factor' => 'decimal:3',
            'energy_kwh' => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
