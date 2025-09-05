<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Dokumen Dibuat per Bulan';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Mengambil jumlah dokumen berdasarkan bulan
        $documentsPerMonth = Document::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Membuat array data untuk chart
        $data = array_fill(1, 12, 0);  // Inisialisasi semua bulan dengan nilai 0

        // Mengisi data berdasarkan hasil query
        foreach ($documentsPerMonth as $month => $total) {
            $data[$month] = $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dokumen',
                    'data' => array_values($data),  // Ambil nilai data untuk chart
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ], // Label untuk bulan
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
