<x-filament::page>
    {{ $this->form }}

    <x-filament::button type="button" wire:click="submit" class="mt-4">
        Import
    </x-filament::button>
</x-filament::page>
