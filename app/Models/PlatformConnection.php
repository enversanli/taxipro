<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformConnection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'credentials' => 'encrypted:array', // Auto-encrypts/decrypts JSON
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
