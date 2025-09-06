<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DocumentChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Dokumen Dibuat per Bulan';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 2;

    use InteractsWithPageFilters;

    protected function getData(): array
    {
        // Mengambil filter tanggal dari halaman
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;

        // Query: filter by date range jika ada, else default tahun berjalan
        $query = Document::query()
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
            )
            ->when(
                !($startDate && $endDate),
                fn($q) =>
                $q->whereYear('created_at', now()->year)
            );

        // Group per bulan (MySQL)
        $perMonth = $query
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')   // [1=>N, 2=>N, ...]
            ->toArray();

        // Zero-fill 12 bulan
        $data = array_fill(1, 12, 0);
        foreach ($perMonth as $m => $total) {
            $data[$m] = (int) $total;
        }

        return [
            'datasets' => [[
                'label' => 'Jumlah Dokumen',
                'data'  => array_values($data), // Jan..Des
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'borderColor'     => 'rgba(75, 192, 192, 1)',
                'borderWidth'     => 1,
            ]],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
