<x-filament-panels::page>
    <div class="mb-4">
        @livewire(\App\Filament\Widgets\LanguageSwitcher::class)
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <x-filament::card>
            <h2 class="text-lg font-bold">{{ __('common.welcome_title') }}</h2>
            <p>{{ __('common.welcome_desc') }}</p>
        </x-filament::card>
        <x-filament::card>
            <h2 class="text-lg font-bold">{{ __('common.drivers_title') }}</h2>
            <p>{{ __('common.drivers_desc') }}</p>
        </x-filament::card>
    </div>
</x-filament-panels::page>
