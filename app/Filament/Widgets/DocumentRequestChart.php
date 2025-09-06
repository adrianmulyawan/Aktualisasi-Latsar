<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRequest;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DocumentRequestChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Permintaan Dokumen per Judul';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;

        $query = DocumentRequest::query()
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

        $documentRequests = $query
            ->selectRaw('request_document_title, COUNT(*) as total')
            ->groupBy('request_document_title')
            ->orderByDesc('total')
            ->get();

        $labels = $documentRequests->pluck('request_document_title')->map(fn($v) => $v ?: 'Tanpa Judul')->toArray();
        $data   = $documentRequests->pluck('total')->map(fn($v) => (int) $v)->toArray();

        return [
            'datasets' => [[
                'label' => 'Permintaan Dokumen',
                'data'  => $data,
                'backgroundColor' => $this->makeColors(count($data)),
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

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
