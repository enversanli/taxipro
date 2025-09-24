<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;

class ImportInvoice extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = InvoiceResource::class;
    protected static string $view = 'filament.resources.invoice-resource.pages.import-invoice';

    /**
     * Initialize the form when the page loads.
     */
    public function mount(): void
    {
        $this->form->fill([]);
    }

    /**
     * Define the form schema.
     */
    protected function getFormSchema(): array
    {
        return [
            Select::make('platform')
                ->label('Platform')
                ->options([
                    'uber' => 'Uber',
                    'bolt' => 'Bolt',
                    'bliq' => 'Bliq',
                    'freenow' => 'Free Now',
                ])
                ->required(),

            Select::make('month')
                ->label('Month')
                ->options(fn() => __('common.months'))
                ->required(),

            FileUpload::make('attachment')
                ->label('Upload CSV / Excel')
                ->directory('imports')
                ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->required(),
        ];
    }

    /**
     * Handle form submission.
     */
    public function submit()
    {
        $data = $this->form->getState();

        // Extract uploaded file path
        $filePath = $data['attachment'];

        // Call service class to process the file
        app(\App\Services\InvoiceImportService::class)
            ->import($filePath, $data['platform'], $data['month']);

        $this->notify('success', 'Invoices imported successfully!');

        return redirect(static::$resource::getUrl('index'));
    }
}
