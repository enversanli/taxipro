<?php

namespace App\Traits;

trait AdminRoleTrait
{
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role === 'admin';  // only admin sees this resource in the menu
    }
}
