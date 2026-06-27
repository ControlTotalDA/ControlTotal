<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'machine_id',
        'metric_id',
        'type',
        'value',
        'threshold',
        'phase',
        'resolved_at',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'threshold' => 'decimal:2',
            'resolved_at' => 'datetime',
            'seen_at' => 'datetime',
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

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function isSeen(): bool
    {
        return $this->seen_at !== null;
    }
}
