<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriverInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Import Invoice')
                ->url(ImportInvoice::getResource()::getUrl('import'))
                ->icon('heroicon-o-link')
                ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceResource\Widgets\MonthlyInvoiceChart::class,
            InvoiceResource\Widgets\MonthlyPlatformInvoiceChart::class,
        ];
    }
}
