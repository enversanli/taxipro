<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceExportService
{
    protected array $columns = [
        'Car', 'Code', 'First Name', 'Last Name',
        'Phone', 'Year', 'Month', 'Taximetre',
        'Uber', 'Bolt', 'Free Now', 'Bliq',
        'Salary'];

    public function exportSingle(int $invoiceId){
        $invoice = Invoice::with(['driver', 'vehicle', 'details'])->findOrFail($invoiceId);
        $fileName = Str::slug($invoice->driver->full_name . ' ' . $invoice->month . ' ' . $invoice->year) . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        return response()->stream(function () use ($invoice) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->columns);

            fputcsv($handle, [
                $invoice->vehicle->license_plate,
                $invoice->vehicle->code,
                $invoice->driver->first_name,
                $invoice->driver->last_name,
                $invoice->driver->phone,
                $invoice->year,
                $invoice->month,
                $invoice->total_income,
                $invoice->details->where('platform', 'uber')->sum('gross'),
                $invoice->details->where('platform', 'bolt')->sum('gross'),
                $invoice->details->where('platform', 'freenow')->sum('gross'),
                $invoice->details->where('platform', 'bliq')->sum('gross'),
                $invoice->driver_salary
                ]);

            fclose($handle);
        }, 200, $headers);
    }
}
