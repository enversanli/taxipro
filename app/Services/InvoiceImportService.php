<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Vehicle;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class InvoiceImportService
{
    protected string $month;
    protected string $platform;
    protected string $week;
    protected $filePath;

    public function import($data)
    {
        $this->platform = $data['platform'];
        $this->month = $data['month'];
        $this->week = $data['week'];
        $this->filePath = $data['attachment'];

        if (!$this->filePath) {
            throw new \Exception('No file uploaded.');
        }

        if (is_array($this->filePath)) {
            $fileData = reset($this->filePath);
        }

        if (!($fileData instanceof TemporaryUploadedFile)) {
            throw new \Exception('Invalid file uploaded.');
        }

        $tempPath = $fileData->getRealPath();

        $rows = [];

        if (($handle = fopen($tempPath, 'r')) !== false) {
            $header = null;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $data;
                } else {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }

        if ($this->platform == 'uber') {
            $cleanedInvoices = $this->parseUberRows($rows);
        }

        foreach ($cleanedInvoices as $cleanedInvoice) {
            $this->createInvoice($cleanedInvoice);
        }

    }

    private function createInvoice($tempInvoice)
    {
        $driver = $this->findDriver('email', $tempInvoice);

        $invoice = Invoice::firstOrCreate(
            [
                'driver_id' => $driver->id,
                'company_id' => $driver->company_id,
                'year' => now()->year,
                'month' => $this->month,
            ],
            [
                'vehicle_id' => Vehicle::first()->id,
                'total_income' => 0,
                'gross' => 0,
                'bar' => 0,
            ]
        );

        $invoice->details()->create([
            'platform' => $this->platform,
            'gross' => $tempInvoice['gross_total'],
            'cash' => $tempInvoice['cash_received'],
            'bar' => $tempInvoice['in_app_payment'],
            'tip' => $tempInvoice['tips'],
            'net' => $tempInvoice['net_revenue'],
            'commission' => $tempInvoice['total_fees'],
        ]);

        $invoice->update([
            'total_income' => $invoice->total_income + $tempInvoice['gross_total'],
            'gross'        => $invoice->gross + $tempInvoice['gross_total'],
            'cash'         => $invoice->cash + $tempInvoice['cash_received'],
            'bar'          => $invoice->bar + $tempInvoice['in_app_payment'],
            'net'          => $invoice->net + $tempInvoice['net_revenue'],
            'tip'          => $invoice->tip + $tempInvoice['tips'],
        ]);

    }

    private function findDriver($key, $invoice)
    {
        $driver = Driver::where($key, $invoice['email'])->first();

        if ($driver) {
            return $driver;
        }

        $fullName = explode(' ', $invoice['name']);

        return Driver::create([
            'first_name' => $fullName[0],
            'last_name' => $fullName[1],
            'company_id' => auth()->user()->company_id,
            'phone' => $invoice['phone'],
            'work_model' => 'taxi',
            'email' => $invoice['email']
        ]);
    }

    private function invoice($month, $platform)
    {
        $invoice = Invoice::where('month', $month)->where('platform', $platform)->first();
        dd($invoice);
    }

    public function parseUberRows(array $rows): array
    {
        $cleanRows = [];

        foreach ($rows as $row) {
            // Clean headers: remove BOM and quotes
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                $cleanKey = preg_replace('/^\x{FEFF}/u', '', $key); // remove BOM
                $cleanKey = trim($cleanKey, "\"");                  // remove quotes
                $cleanedRow[$cleanKey] = $value;
            }

            $cleanRows[] = [
                'name' => $cleanedRow['Fahrer:in'] ?? null,
                'email' => $cleanedRow['E-Mail-Adresse'] ?? null,
                'phone' => $cleanedRow['Handynummer'] ?? null,
                'gross_total' => $cleanedRow['Bruttoverdienst (insgesamt)|€'] ?? null,
                'in_app_payment' => $cleanedRow['Bruttoeinnahmen (In-App-Zahlung)|€'] ?? null,
                'in_app_payment_tax' => $cleanedRow['Bruttoeinnahmen (In-App-Zahlung) MwSt.|€'] ?? null,
                'cash_payment' => $cleanedRow['Bruttoeinnahmen (Barzahlung)|€'] ?? null,
                'cash_payment_tax' => $cleanedRow['Bruttoeinnahmen (Barzahlung) MwSt.|€'] ?? null,
                'cash_received' => $cleanedRow['Erhaltenes Bargeld|€'] ?? null,
                'tips' => $cleanedRow['Trinkgelder von Fahrgästen|€'] ?? null,
                'campaign_income' => $cleanedRow['Kampagneneinnahmen|€'] ?? null,
                'reimbursements' => $cleanedRow['Kostenerstattungen|€'] ?? null,
                'cancellation_fees' => $cleanedRow['Stornogebühren|€'] ?? null,
                'cancellation_fees_tax' => $cleanedRow['Stornogebühren MwSt.|€'] ?? null,
                'toll_fees' => $cleanedRow['Mautgebühren|€'] ?? null,
                'booking_fees' => $cleanedRow['Buchungsgebühren|€'] ?? null,
                'booking_fees_tax' => $cleanedRow['MwSt. auf Buchungsgebühren|€'] ?? null,
                'total_fees' => $cleanedRow['Gesamtgebühren|€'] ?? null,
                'commission' => $cleanedRow['Provision|€'] ?? null,
                'refunds' => $cleanedRow['Rückerstattungen an Fahrgäste|€'] ?? null,
                'other_fees' => $cleanedRow['Sonstige Gebühren|€'] ?? null,
                'net_revenue' => $cleanedRow['Umsatz netto|€'] ?? null,
                'expected_payout' => $cleanedRow['Voraussichtliche Auszahlung|€'] ?? null,
                'gross_per_hour' => $cleanedRow['Bruttoverdienst pro Stunde|€/Std.'] ?? null,
                'net_per_hour' => $cleanedRow['Nettoverdienst pro Stunde|€/Std.'] ?? null,
                'driver_id' => $cleanedRow['Fahrer-ID'] ?? null,
                'unique_id' => $cleanedRow['Individueller Identifikator'] ?? null,
            ];
        }

        return $cleanRows;
    }


}


