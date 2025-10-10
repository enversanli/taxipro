<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;

class LanguageSwitcher extends Widget implements Forms\Contracts\HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.language-switcher';
    protected int|string|array $columnSpan = 1; // small width for header

    public $locale;

    public function mount(): void
    {
        $this->form->fill([
            'locale' => session('locale', config('app.locale')),
        ]);
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
                ]),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();
        session(['locale' => $data['locale']]);
        app()->setLocale($data['locale']);

        return redirect()->back();
    }
}
