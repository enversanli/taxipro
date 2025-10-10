<?php

namespace App\Providers;

use App\Filament\Widgets\LanguageSwitcher;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Nothing needed here for widgets
    }

    public static function getWidgets(): array
    {
        return [
            LanguageSwitcher::class,
        ];
    }
}
