<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformConnection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
