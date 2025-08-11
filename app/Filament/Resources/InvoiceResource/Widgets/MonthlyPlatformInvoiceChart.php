<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyPlatformInvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $invoiceDetails = DB::table('invoice_details')
            ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
            // cast month to integer so '08' and '8' match
            ->select(
                DB::raw('CAST(invoices.month AS INTEGER) as month'),
                'invoice_details.platform',
                DB::raw('SUM(invoice_details.gross) as gross_total'),
                DB::raw('SUM(invoice_details.net) as net_total'),
                DB::raw('SUM(invoice_details.cash) as cash_total'),
                DB::raw('SUM(invoice_details.tip) as tip_total'),
                DB::raw('SUM(invoice_details.bar) as bar_total')
            )
            ->groupBy('month', 'invoice_details.platform')
            ->orderBy('month')
            ->get();

        // labels: Jan ... Dec
        $labels = collect(range(1, 12))
            ->map(fn($m) => Carbon::createFromFormat('!m', $m)->format('M'))
            ->toArray();

        $platforms = ['uber', 'bolt', 'bliq', 'freenow'];

        // nice palette for the platforms
        $palette = [
            '#3b82f6', // blue - uber
            '#ef4444', // red  - bolt
            '#10b981', // green - bliq
            '#f59e0b', // amber - freenow
        ];

        $datasets = [];

        foreach ($platforms as $index => $platform) {
            // key by integer month for fast lookups
            $rows = $invoiceDetails
                ->where('platform', $platform)
                ->keyBy(fn($r) => (int) $r->month);

            $data = [];
            foreach (range(1, 12) as $m) {
                // $rows[$m] will be null if no data -> 0
                $data[] = isset($rows[$m]) ? (float) $rows[$m]->gross_total : 0;
            }

            $color = $palette[$index] ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF));

            $datasets[] = [
                'label' => ucfirst($platform),
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => $this->hexToRgba($color, 0.15),
                'fill' => true,
                'tension' => 0.2,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Helper to convert hex to rgba string for backgroundColor
     */
    protected function hexToRgba(string $hex, float $alpha = 1.0): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }


    protected function getType(): string
    {
        return 'line';
    }
}
