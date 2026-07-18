<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'owner_name', 'owner_email', 'plan_id',
        'price_override', 'module_overrides', 'primary_color',
        'status', 'trial_ends_at',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'module_overrides' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The module keys this tenant may use: explicit per-tenant overrides if set,
     * otherwise whatever their plan includes.
     */
    public function enabledModuleKeys(): array
    {
        if (is_array($this->module_overrides)) {
            return $this->module_overrides;
        }
        return $this->plan?->module_keys ?? [];
    }

    public function allowsModule(string $key): bool
    {
        return in_array($key, $this->enabledModuleKeys(), true);
    }

    /** The effective monthly price: per-tenant override, else the plan price. */
    public function effectivePrice(): ?string
    {
        return $this->price_override ?? $this->plan?->price;
    }
}
