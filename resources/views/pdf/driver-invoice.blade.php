{{-- resources/views/pdf/invoice.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Invoice - {{ $invoice->driver->first_name }} {{ $invoice->driver->last_name }}</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; sans-serif; font-size: 13px; color: #333; }
        h1, h2, h3 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
        .summary-table td { font-weight: bold; }
        .section-title { margin-top: 20px; font-size: 16px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
    </style>
</head>
<body>

<h1>Driver Invoice Report</h1>
<p><strong>Driver:</strong> {{ $invoice->driver->first_name }} {{ $invoice->driver->last_name }}</p>
<p><strong>Company:</strong> {{ $invoice->company->name ?? '-' }}</p>
<p><strong>Month/Year:</strong> {{ $invoice->month }} / {{ $invoice->year }}</p>
<p><strong>Generated at:</strong> {{ now()->format('d.m.Y H:i') }}</p>

<div class="section-title">Platform Details</div>
<table>
    <thead>
    <tr>
        <th>Platform</th>
        <th class="text-right">Gross (€)</th>
        <th class="text-right">Tip (€)</th>
        <th class="text-right">Bar (€)</th>
        <th class="text-right">Net (€)</th>
    </tr>
    </thead>
    <tbody>

    @foreach ($invoice->details as $detail)
    <tr>
        <td>{{ ucfirst($detail->platform) }}</td>
        <td class="text-right">{{ number_format($detail->gross, 2) }}</td>
        <td class="text-right">{{ number_format($detail->tip, 2) }}</td>
        <td class="text-right">{{ number_format($detail->bar, 2) }}</td>
        <td class="text-right">{{ number_format($detail->net, 2) }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">Summary</div>
<table class="summary-table">
    <tr>
        <td>Total Gross</td>
        <td class="text-right">€{{ number_format($invoice->gross, 2) }}</td>
    </tr>
    <tr>
        <td>Total Tip</td>
        <td class="text-right">€{{ number_format($invoice->tip, 2) }}</td>
    </tr>
    <tr>
        <td>Total Bar</td>
        <td class="text-right">€{{ number_format($invoice->bar, 2) }}</td>
    </tr>
    <tr>
        <td>Total Cash</td>
        <td class="text-right">€{{ number_format($invoice->cash, 2) }}</td>
    </tr>
    <tr>
        <td>Total Net</td>
        <td class="text-right">€{{ number_format($invoice->net, 2) }}</td>
    </tr>
    <tr>
        <td>Driver Salary</td>
        <td class="text-right">€{{ number_format($invoice->driver_salary, 2) }}</td>
    </tr>
</table>

<p style="margin-top: 25px; font-size: 12px; color: #777;">
    Generated automatically by the system. This document is not a legal invoice.
</p>
</body>
</html>
