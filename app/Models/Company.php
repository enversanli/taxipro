<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    use CommonTrait;

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

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
