<?php

namespace App\Filament\Widgets;

use App\Models\DocumentRequest;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class DocumentRequestStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Permintaan Dokumen Berdasarkan Status';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '235px';

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

        $query = DocumentRequest::query()
            // (Opsional) batasi data untuk guest hanya miliknya
            ->when(
                Auth::user()?->hasRole('guest'),
                fn($q) =>
                $q->where('user_id', Auth::id())
            )
            // filter tanggal jika ada, kalau tidak ada pakai tahun berjalan
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

        // Ambil agregat per status (hanya pending & completed)
        $rows = $query->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['pending', 'completed'])
            ->groupBy('status')
            ->get();

        // Susun urutan label tetap (agar posisi tidak lompat-lompat)
        $orderedStatuses = ['pending', 'completed'];
        $totalsByStatus = collect($orderedStatuses)
            ->mapWithKeys(fn($s) => [$s => 0])
            ->merge($rows->pluck('total', 'status')->toArray());

        $labels = ['Pending', 'Completed'];
        $data   = array_values($totalsByStatus->toArray());

        // Warna konsisten dengan key 'completed' (bukan 'complete')
        $colors = [
            'pending'   => '#FF6384',
            'completed' => '#36A2EB',
        ];
        $backgroundColors = $orderedStatuses
            ? array_map(fn($s) => $colors[$s] ?? '#FF9F40', $orderedStatuses)
            : ['#FF9F40', '#36A2EB'];

        return [
            'datasets' => [[
                'label' => 'Jumlah Permintaan Dokumen',
                'data'  => $data,
                'backgroundColor' => $backgroundColors,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
