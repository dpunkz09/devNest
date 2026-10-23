<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    /**
     * Read a setting, falling back to the default when it is missing.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allCached()[$key] ?? null;

        return $value ?? $default;
    }

    /**
     * All settings, cached as a key/value map.
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('settings:all', function (): array {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    /**
     * Store a setting and drop the cached map.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget('settings:all');
    }

    /**
     * Drop the cached settings map.
     */
    public static function flushCache(): void
    {
        Cache::forget('settings:all');
    }
}
