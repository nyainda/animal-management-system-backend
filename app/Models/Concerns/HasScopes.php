<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasScopes
{
    public function scopeByType(Builder $query, string $type = 'all'): Builder
    {
        return $type === 'all' ? $query : $query->where('type', $type);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
