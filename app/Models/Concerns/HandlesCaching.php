<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;

trait HandlesCaching
{
    protected static int $cacheTtlMinutes = 60;

    public static function getCachedById(string $id, string $userId): ?self
    {
        return Cache::remember(
            "animal_{$id}_user_{$userId}",
            now()->addMinutes(static::$cacheTtlMinutes),
            fn() => self::where('id', $id)->forUser($userId)->first()
        );
    }

    public static function setCacheTtl(int $minutes): void
    {
        static::$cacheTtlMinutes = $minutes;
    }

    public static function getCacheTtl(): int
    {
        return static::$cacheTtlMinutes;
    }
}
