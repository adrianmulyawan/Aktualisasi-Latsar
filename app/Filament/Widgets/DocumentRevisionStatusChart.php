<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRevision;
use Filament\Widgets\ChartWidget;

class DocumentRevisionStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Revisi Dokumen';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '235px';

    protected function getData(): array
    {
        // Ambil jumlah revisi berdasarkan status
        $revisionStatuses = DocumentRevision::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')  // Group by berdasarkan status
            ->get();

        // Siapkan data untuk chart
        $labels = $revisionStatuses->pluck('status')->toArray();  // Status dokumen
        $data = $revisionStatuses->pluck('total')->toArray();  // Jumlah revisi per status

        // Tentukan warna chart untuk setiap status
        $colors = [
            'draft' => '#FF6384',   // Merah untuk Draft
            'review' => '#FFCE56',  // Kuning untuk Proses Review
            'approved' => '#36A2EB', // Biru untuk Disetujui
            'rejected' => '#4BC0C0', // Hijau untuk Ditolak
        ];

        // Tentukan warna berdasarkan status
        $backgroundColor = $revisionStatuses->map(function ($item) use ($colors) {
            return $colors[$item->status] ?? '#FF9F40'; // Default warna oranye jika status tidak dikenali
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Revisi Dokumen',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor, // Gunakan warna berdasarkan status
                ],
            ],
            'labels' => $labels,  // Label status
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
