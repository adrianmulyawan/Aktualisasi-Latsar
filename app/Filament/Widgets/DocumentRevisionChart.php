<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRevision;
use Filament\Widgets\ChartWidget;

class DocumentRevisionChart extends ChartWidget
{
    protected static ?string $heading = 'Revisi Dokumen Berdasarkan Bulan';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        // Ambil jumlah revisi dokumen berdasarkan bulan
        $documentRevisions = DocumentRevision::selectRaw('MONTH(revision_date) as month, YEAR(revision_date) as year, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Siapkan data untuk chart
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Data jumlah revisi per bulan
        $data = array_fill(0, 12, 0);  // Inisialisasi data untuk 12 bulan dengan nilai 0

        // Mengisi data berdasarkan hasil query
        foreach ($documentRevisions as $revision) {
            $monthIndex = $revision->month - 1;  // Indeks bulan (0-11)
            $data[$monthIndex] = $revision->total;  // Menyimpan jumlah revisi per bulan
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Revisi Dokumen',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB', // Biru
                    'borderColor' => '#36A2EB', // Biru
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $months, // Label bulan
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
