<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DocumentCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Dokumen per Kategori';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '235px';

    use InteractsWithPageFilters;


    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;

        // Base query + filter tanggal (default: tahun berjalan bila filter kosong)
        $query = Document::query()
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('documents.created_at', [$startDate, $endDate])
            )
            ->when(
                !($startDate && $endDate),
                fn($q) =>
                $q->whereYear('documents.created_at', now()->year)
            );

        // Group by kategori (LEFT JOIN agar kategori null tetap terhitung)
        $rows = $query
            ->leftJoin('categories', 'categories.id', '=', 'documents.category_id')
            ->selectRaw('COALESCE(categories.name, ?) as name, COUNT(*) as total', ['Tanpa Kategori'])
            ->groupBy('name')
            ->orderByDesc('total')
            ->get();

        $labels = $rows->pluck('name')->toArray();
        $data   = $rows->pluck('total')->map(fn($v) => (int) $v)->toArray();

        return [
            'datasets' => [[
                'label' => 'Jumlah Dokumen',
                'data'  => $data,
                'backgroundColor' => $this->makeColors(count($data)),
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // atau 'doughnut' jika ingin donut
    }

    /**
     * Generate warna secukupnya (ulang palet jika kategori > palet).
     */
    private function makeColors(int $n): array
    {
        $palette = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#8DD3C7',
            '#FDB462',
            '#80B1D3',
            '#FB8072',
            '#B3DE69',
            '#BC80BD',
        ];
        $colors = [];
        for ($i = 0; $i < $n; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }
        return $colors;
    }
}
