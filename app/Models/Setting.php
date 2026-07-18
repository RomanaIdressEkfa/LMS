<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        if (! $row) {
            return $default;
        }

        return match ($row->type) {
            'bool' => filter_var($row->value, FILTER_VALIDATE_BOOLEAN),
            'int' => (int) $row->value,
            'json' => json_decode($row->value, true),
            default => $row->value,
        };
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group,
                'type' => $type,
            ]
        );
        Cache::forget('settings.all');
    }
}
