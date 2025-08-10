<?php

namespace App\Traits;

trait UserTrait
{
    public function isAdmin(){
        return $this->role == 'admin';
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
