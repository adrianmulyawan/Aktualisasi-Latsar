<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRevision;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DocumentRevisionStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Status Revisi Dokumen';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '235px';

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;

        // Base query: pakai revision_date; kalau filter kosong → tahun berjalan
        $query = DocumentRevision::query()
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('revision_date', [$startDate, $endDate])
            )
            ->when(
                !($startDate && $endDate),
                fn($q) =>
                $q->whereYear('revision_date', now()->year)
            );

        $rows = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status'); // ['draft'=>N, ...]

        // Urutan & label tetap
        $ordered = ['draft', 'review', 'approved', 'rejected'];
        $labels  = ['Draft', 'Proses Review', 'Disetujui', 'Ditolak'];

        // Pastikan status yang tidak ada tetap 0
        $data = [];
        foreach ($ordered as $s) {
            $data[] = (int) ($rows[$s] ?? 0);
        }

        // Warna konsisten
        $colors = [
            '#FF6384', // draft
            '#FFCE56', // review
            '#36A2EB', // approved
            '#4BC0C0', // rejected
        ];

        return [
            'datasets' => [[
                'label' => 'Jumlah Revisi Dokumen',
                'data'  => $data,
                'backgroundColor' => $colors,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // bisa diganti 'doughnut' kalau mau
    }
}
