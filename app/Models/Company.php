<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'phone',
        'email',
    ];

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }
}
