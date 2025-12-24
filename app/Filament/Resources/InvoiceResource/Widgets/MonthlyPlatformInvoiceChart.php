<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyPlatformInvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Aylık Platform Gelir Analizi';

    protected function getData(): array
    {
        // ÖNEMLİ: Sorguda sadece var olan yeni sütun isimlerini kullanıyoruz.
        // SQL hatasını önlemek için alias (takma isim) kullanılmıştır.
        $invoiceDetails = DB::table('invoice_details')
            ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->select(
                DB::raw('CAST(invoices.month AS INTEGER) as month_int'),
                'invoice_details.platform',
                // Yeni migration sütunları burada toplanıyor
                DB::raw('SUM(invoice_details.gross_amount) as total_gross'),
                DB::raw('SUM(invoice_details.net_payout) as total_net'),
                DB::raw('SUM(invoice_details.cash_collected_by_driver) as total_cash')
            )
            ->groupBy('month_int', 'invoice_details.platform')
            ->orderBy('month_int')
            ->get();

        // Grafik etiketleri: Jan ... Dec
        $labels = collect(range(1, 12))
            ->map(fn($m) => Carbon::createFromFormat('!m', $m)->translatedFormat('M'))
            ->toArray();

        $platforms = ['uber', 'bolt', 'bliq', 'freenow'];

        $palette = [
            '#3b82f6', // Uber - Mavi
            '#10b981', // Bolt - Yeşil
            '#f59e0b', // Bliq - Turuncu
            '#ef4444', // Free Now - Kırmızı
        ];

        $datasets = [];

        foreach ($platforms as $index => $platform) {
            // Ay bazlı veriyi hızlıca çekmek için keyBy kullanıyoruz
            $rows = $invoiceDetails
                ->where('platform', $platform)
                ->keyBy(fn($r) => (int) $r->month_int);

            $data = [];
            foreach (range(1, 12) as $m) {
                // Eğer o ay için veri yoksa 0 döndür
                $data[] = isset($rows[$m]) ? (float) $rows[$m]->total_gross : 0;
            }

            $color = $palette[$index] ?? '#cccccc';

            $datasets[] = [
                'label' => ucfirst($platform),
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => $this->hexToRgba($color, 0.15),
                'fill' => true,
                'tension' => 0.4, // Daha yumuşak hatlı çizgiler
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Renklerin şeffaf arka planı için yardımcı fonksiyon
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
