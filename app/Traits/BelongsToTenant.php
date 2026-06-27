<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where(
                    $builder->getModel()->getTable().'.tenant_id',
                    auth()->user()->tenant_id
                );
            }
        });

        static::creating(function ($model): void {
            if (auth()->check() && auth()->user()->tenant_id && ! $model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
