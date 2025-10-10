<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ImportInvoice extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Invoice Report';
    protected static ?string $title = 'Import Invoice';
    protected static bool $shouldRegisterNavigation = true;

    public $platform;
    public $month;
    public $week;        // <-- This is REQUIRED
    public $attachment;  // <-- Required for file uploads

    protected static string $resource = InvoiceResource::class;
    protected static string $view = 'filament.resources.invoice-resource.pages.import-invoice';

    public static function getNavigationGroup(): ?string
    {
        return __('common.invoices');
    }

    public function mount(): void
    {
        $this->form->fill([
            'platform' => null,
            'month' => null,
            'week' => null,
            'attachment' => null,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('platform')
                ->label('Platform')
                ->options([
                    'uber' => 'Uber',
                    'bolt' => 'Bolt',
                    'bliq' => 'Bliq',
                    'freenow' => 'Free Now',
                ])
                ->required(),

            Forms\Components\Select::make('month')
                ->label('Month')
                ->options(fn() => __('common.months'))
                ->required(),

            Forms\Components\Select::make('week')
                ->label('Week')
                ->options([
                    '1' => 'Week 1',
                    '2' => 'Week 2',
                    '3' => 'Week 3',
                    '4' => 'Week 4',
                ])
                ->required()
                ->visible(fn(callable $get) => $get('platform') === 'uber'),

            FileUpload::make('attachment')
                ->label('Upload CSV / Excel')
                ->directory('imports')
                ->acceptedFileTypes([
                    'text/csv',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])
                ->required()
        ];
    }

    public function submit()
    {
        $data = $this->form->validate();

        app(\App\Services\InvoiceImportService::class)
            ->import($data);

        Notification::make()
            ->title('Invoices imported successfully!')
            ->success()
            ->send();

        return redirect(static::$resource::getUrl('index'));
    }
}

