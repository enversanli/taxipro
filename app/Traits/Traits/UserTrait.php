<?php

namespace App\Traits\Traits;

trait UserTrait
{
    public function isAdmin(){
        return $this->role == 'admin';
    }
}
