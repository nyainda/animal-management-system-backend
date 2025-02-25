<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait GeneratesInternalId
{
    protected static function bootGeneratesInternalId()
    {
        static::creating(function ($model) {
            $model->internal_id = self::generateUniqueInternalId($model->type);
        });
    }

protected static function generateUniqueInternalId($type): string
{
    $date = Carbon::now();
    $year = $date->format('y');
    $prefix = strtoupper(substr($type, 0, 3)); // First 3 letters of animal type
    $pattern = "{$prefix}/{$year}/%";

    // Get the latest sequential number for this type and year
    $latestNumber = DB::table('animals')
        ->where('internal_id', 'like', $pattern)
        ->orderByRaw("CAST(REGEXP_REPLACE(internal_id, '.*/', '') AS INTEGER) DESC")
        ->value('internal_id');

    if ($latestNumber) {
        $sequence = (int)explode('/', $latestNumber)[2] + 1;
    } else {
        $sequence = 1;
    }

    return sprintf('%s/%s/%04d', $prefix, $year, $sequence);
}
}
