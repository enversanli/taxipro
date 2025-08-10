<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait CommonTrait
{
    public static function bootCommonTrait()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && !Auth::user()->isAdmin()) {
                $builder->where('company_id', Auth::user()->company_id);
            }
        });
    }
}
