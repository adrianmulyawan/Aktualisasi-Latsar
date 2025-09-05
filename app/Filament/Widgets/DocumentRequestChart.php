<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRequest;
use Filament\Widgets\ChartWidget;

class DocumentRequestChart extends ChartWidget
{
    protected static ?string $heading = 'Permintaan Dokumen per Judul';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Ambil jumlah permintaan berdasarkan request_document_title
        $documentRequests = DocumentRequest::selectRaw('request_document_title, COUNT(*) as total')
            ->groupBy('request_document_title')  // Group by berdasarkan request_document_title
            ->get();

        // Siapkan data untuk chart
        $labels = $documentRequests->pluck('request_document_title')->toArray();  // Judul dokumen
        $data = $documentRequests->pluck('total')->toArray();  // Jumlah permintaan

        return [
            'datasets' => [
                [
                    'label' => 'Permintaan Dokumen',
                    'data' => $data,
                    'backgroundColor' => [
                        '#FF6384', // merah muda
                        '#36A2EB', // biru
                        '#FFCE56', // kuning
                        '#4BC0C0', // hijau kebiruan
                        '#9966FF', // ungu
                        '#FF9F40', // oranye
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
