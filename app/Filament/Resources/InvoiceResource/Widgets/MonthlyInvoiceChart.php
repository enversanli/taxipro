<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyInvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Aylık Kazanç ve Kasa Analizi';

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public ?string $month = null;
    public ?int $driver_id = null;

    protected function getData(): array
    {
        // 1. Ana verileri (Taxameter, Maaş, Nakit) Invoices tablosundan çekiyoruz
        $query = Invoice::select(
            'month',
            DB::raw('SUM(taxameter_total) as total_taxameter'),
            DB::raw('SUM(net_salary) as total_salary'),
            DB::raw('SUM(expected_cash) as total_expected_cash')
        )
            ->groupBy('month')
            ->orderBy('month');

        // 2. Uygulama bazlı toplam Brüt ve Bahşişi detay tablosundan (JOIN ile) çekiyoruz
        $query->leftJoin('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->addSelect(
                DB::raw('SUM(invoice_details.gross_amount) as total_app_gross'),
                DB::raw('SUM(invoice_details.tip) as total_tips')
            );

        if ($this->month) {
            $query->where('invoices.month', $this->month);
        }

        if ($this->driver_id) {
            $query->where('invoices.driver_id', $this->driver_id);
        }

        $invoices = $query->get();

        // Etiketleri oluştur (Jan, Feb...)
        $labels = $invoices->map(function ($item) {
            try {
                return Carbon::createFromFormat('m', $item->month)->translatedFormat('M');
            } catch (\Exception $e) {
                return $item->month;
            }
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Taksimetre (Fiziksel)',
                    'data' => $invoices->pluck('total_taxameter')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'App Brüt (Uber/Bolt/v.b.)',
                    'data' => $invoices->pluck('total_app_gross')->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Şoför Maaşı',
                    'data' => $invoices->pluck('total_salary')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Beklenen Nakit (Kasa)',
                    'data' => $invoices->pluck('total_expected_cash')->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Toplam Bahşiş',
                    'data' => $invoices->pluck('total_tips')->toArray(),
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function updateFilters(array $filters): void
    {
        $this->month = $filters['month'] ?? null;
        $this->driver_id = $filters['driver_id'] ?? null;
        $this->resetChartData();
    }

    public function resetChartData()
    {
        $this->dispatch('$refresh');
    }
}
