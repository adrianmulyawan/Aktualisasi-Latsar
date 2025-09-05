<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Dokumen per Kategori';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '235px';


    protected function getData(): array
    {
        // Ambil jumlah dokumen per kategori
        $documentsByCategory = Document::selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->with('category:id,name') // load nama kategori
            ->get();

        // Siapkan label (nama kategori)
        $labels = $documentsByCategory->map(function ($item) {
            return $item->category->name ?? 'Tanpa Kategori';
        })->toArray();

        // Siapkan data (jumlah dokumen per kategori)
        $data = $documentsByCategory->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dokumen',
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
        return 'pie';
    }
}
