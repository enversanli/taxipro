<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyInvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Invoices';

    protected function getData(): array
    {
        $invoices = Invoice::select(
            'month',
            DB::raw('SUM(total_income) as total_income'),
            DB::raw('SUM(gross) as gross_total'),
            DB::raw('SUM(net) as net_total'),
            DB::raw('SUM(cash) as cash_total'),
            DB::raw('SUM(tip) as tip_total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $invoices->map(fn($item) => Carbon::createFromFormat('m', $item->month)->format('M'))->toArray();


        return [
            'datasets' => [
                [
                    'label' => 'Taximaetre',
                    'data' => $invoices->pluck('total_income')->toArray(),
                    'borderColor' => '#3b82f6', // blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Gross',
                    'data' => $invoices->pluck('gross_total')->toArray(),
                    'borderColor' => '#ef4444', // red
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Net',
                    'data' => $invoices->pluck('net_total')->toArray(),
                    'borderColor' => '#10b981', // green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Cash',
                    'data' => $invoices->pluck('cash_total')->toArray(),
                    'borderColor' => '#f59e0b', // amber
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Tip',
                    'data' => $invoices->pluck('tip_total')->toArray(),
                    'borderColor' => '#8b5cf6', // purple
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];

    }

    protected function getType(): string
    {
        return 'line';
    }
}
