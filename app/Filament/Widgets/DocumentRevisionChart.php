<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRevision;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class DocumentRevisionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Revisi Dokumen Berdasarkan Bulan';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 4;

    // Menambahkan pengecekan role pada widget
    public static function canView(): bool
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Cek apakah user memiliki role yang sesuai
        return $user && $user->hasAnyRole(['super_admin', 'user', 'kepala_dinas']);
    }

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? null;
        $endDate   = $this->filters['end_date'] ?? null;

        // Query dengan filter tanggal
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

        // Ambil jumlah per bulan
        $documentRevisions = $query
            ->selectRaw('MONTH(revision_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Siapkan data chart
        $months = [
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
        ];

        $data = array_fill(1, 12, 0); // Key mulai dari 1 (Jan)
        foreach ($documentRevisions as $month => $total) {
            $data[$month] = $total;
        }

        return [
            'datasets' => [[
                'label' => 'Jumlah Revisi Dokumen',
                'data' => array_values($data),
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#36A2EB',
                'borderWidth' => 1,
            ]],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
