<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Widget implements Forms\Contracts\HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.language-switcher';
    protected int|string|array $columnSpan = 1;

    public $locale;

    public function mount(): void
    {
        $this->locale = session('locale', config('app.locale'));
        $this->form->fill(['locale' => $this->locale]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('locale')
                ->label(false)
                ->options([
                    'en' => 'English',
                    'tr' => 'Türkçe',
                    'de' => 'Deutsch',
                ])
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->submit($state)),
        ];
    }

    public function submit($locale): void
    {
        redirect()->route('set-locale', ['locale' => $locale]);
    }

    protected function getViewData(): array
    {
        return [
            'form' => $this->form,
        ];
    }
}
