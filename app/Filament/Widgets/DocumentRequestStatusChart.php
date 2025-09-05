<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRequest;
use Filament\Widgets\ChartWidget;

class DocumentRequestStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Permintaan Dokumen Berdasarkan Status';
    protected static ?string $pollingInterval = '10s'; // Memperbarui chart setiap 10 detik (opsional)
    protected static ?int $sort = 3;  // Penempatan widget (opsional)
    protected static ?string $maxHeight = '235px'; // Atur tinggi maksimum widget (opsional)

    protected function getData(): array
    {
        // Ambil jumlah permintaan berdasarkan status dokumen
        $documentRequests = DocumentRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')  // Group by berdasarkan status
            ->whereIn('status', ['pending', 'completed']) // Filter hanya status 'pending' dan 'complete'
            ->get();

        // Siapkan data untuk chart
        $labels = $documentRequests->pluck('status')->toArray();  // Status dokumen (pending, complete)
        $data = $documentRequests->pluck('total')->toArray();  // Jumlah permintaan per status

        // Tentukan warna chart untuk setiap status
        $colors = [
            'pending' => '#FF6384',  // Merah untuk pending
            'complete' => '#36A2EB', // Biru untuk complete
        ];

        // Tentukan warna berdasarkan status
        $backgroundColor = $documentRequests->map(function ($item) use ($colors) {
            return $colors[$item->status] ?? '#FF9F40'; // Default warna oranye jika status tidak dikenali
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Permintaan Dokumen',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor, // Gunakan warna berdasarkan status
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
