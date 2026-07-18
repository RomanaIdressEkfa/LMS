<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'icon', 'category',
        'enabled', 'is_core', 'settings', 'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_core' => 'boolean',
        'settings' => 'array',
    ];

    /** Quick check used across the app: is a module turned on? */
    public static function isEnabled(string $key): bool
    {
        return (bool) static::query()->where('key', $key)->value('enabled');
    }
}
