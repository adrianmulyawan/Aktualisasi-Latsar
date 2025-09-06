<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentRevision;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        // Mengambil filter tanggal dari halaman
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;

        $stats = [
            Stat::make('Jumlah Pengguna', Auth::user()->count())
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Kategori Dokumen', Category::count())
                ->icon('heroicon-o-numbered-list')
                ->color('success'),

            Stat::make(
                'Total Dokumen',
                Document::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })->count()
            )
                ->icon('heroicon-o-document')
                ->color('success'),
        ];

        if (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user')) {
            $stats[] = Stat::make(
                'Total Permintaan Dokumen',
                DocumentRequest::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })->count()
            )->icon('heroicon-o-clipboard')->color('success');

            $stats[] = Stat::make(
                'Permintaan Dokumen (Pending)',
                DocumentRequest::where('status', 'pending')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-clipboard')->color('success');

            $stats[] = Stat::make(
                'Permintaan Dokumen (Komplit)',
                DocumentRequest::where('status', 'completed')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-clipboard')->color('success');

            $stats[] = Stat::make(
                'Revisi Dokumen (Draft)',
                DocumentRevision::where('status', 'draft')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-beaker')->color('success');

            $stats[] = Stat::make(
                'Revisi Dokumen (Proses Review)',
                DocumentRevision::where('status', 'review')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-beaker')->color('success');

            $stats[] = Stat::make(
                'Revisi Dokumen (Disetujui)',
                DocumentRevision::where('status', 'approved')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-beaker')->color('success');
        } elseif (Auth::user()->hasRole('guest')) {
            $stats[] = Stat::make(
                'Total Permintaan Dokumen',
                DocumentRequest::where('user_id', Auth::user()->id)
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-clipboard')->color('primary');

            $stats[] = Stat::make(
                'Permintaan Dokumen (Pending)',
                DocumentRequest::where('user_id', Auth::user()->id)
                    ->where('status', 'pending')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-clipboard')->color('primary');

            $stats[] = Stat::make(
                'Permintaan Dokumen (Komplit)',
                DocumentRequest::where('user_id', Auth::user()->id)
                    ->where('status', 'completed')
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count()
            )->icon('heroicon-o-clipboard')->color('primary');
        }

        return $stats;
    }
}
