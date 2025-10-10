<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
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


    public function displaySingle($invoiceId)
    {
        $invoice = Invoice::with(['driver', 'company', 'details'])->findOrFail($invoiceId);

        // Load the PDF view with proper options
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.driver-invoice', ['invoice' => $invoice])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',   // fully UTF-8 compatible
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);


        // Generate safe filename for download
        $fileName = "Invoice-{$invoice->driver->first_name}-{$invoice->month}-{$invoice->year}.pdf";

        // Replace filesystem-unsafe characters
        $safeFileName = preg_replace('/[\/\\\?%*:|"<>]/u', '_', $fileName);

        // Browser view
        if (request()->query('view') === 'browser') {
            return $pdf->stream($safeFileName);
        }

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($safeFileName) . '"; filename*=UTF-8\'\'' . rawurlencode($safeFileName),
        ]);
        // return $pdf->download($safeFileName);
    }
}
