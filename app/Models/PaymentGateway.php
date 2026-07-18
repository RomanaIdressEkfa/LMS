<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'key', 'name', 'logo', 'enabled', 'test_mode',
        'credentials', 'currency', 'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'test_mode' => 'boolean',
        'credentials' => 'encrypted:array', // secrets encrypted at rest
    ];

    /** Hide raw credentials from API responses by default. */
    protected $hidden = ['credentials'];
}
