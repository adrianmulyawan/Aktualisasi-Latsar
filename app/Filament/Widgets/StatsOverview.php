<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\DocumentRevision;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [
            Stat::make('Jumlah Pengguna', Auth::user()->count())
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Kategori Dokumen', Category::count())
                ->icon('heroicon-o-numbered-list')
                ->color('success'),
            Stat::make('Total Dokumen', Document::count())
                ->icon('heroicon-o-document')
                ->color('success'),
        ];

        // Cek role pengguna
        if (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user')) {
            // Untuk super_admin atau user, tampilkan permintaan dokumen yang lebih lengkap
            $stats[] = Stat::make('Total Permintaan Dokumen', DocumentRequest::count())
                ->icon('heroicon-o-clipboard')
                ->color('success');
            $stats[] = Stat::make('Permintaan Dokumen (Pending)', DocumentRequest::where('status', 'pending')->count())
                ->icon('heroicon-o-clipboard')
                ->color('success');
            $stats[] = Stat::make('Permintaan Dokumen (Komplit)', DocumentRequest::where('status', 'completed')->count())
                ->icon('heroicon-o-clipboard')
                ->color('success');
            $stats[] = Stat::make('Revisi Dokumen (Draft)', DocumentRevision::where('status', 'draft')->count())
                ->icon('heroicon-o-beaker')
                ->color('success');
            $stats[] = Stat::make('Revisi Dokumen (Proses Review)', DocumentRevision::where('status', 'review')->count())
                ->icon('heroicon-o-beaker')
                ->color('success');
            $stats[] = Stat::make('Revisi Dokumen (Disetujui)', DocumentRevision::where('status', 'approved')->count())
                ->icon('heroicon-o-beaker')
                ->color('success');
        } elseif (Auth::user()->hasRole('guest')) {
            // Untuk guest, tampilkan hanya permintaan dokumen yang dibuat oleh mereka
            $stats[] = Stat::make('Total Permintaan Dokumen', DocumentRequest::where('user_id', Auth::user()->id)->count())
                ->icon('heroicon-o-clipboard')
                ->color('primary');
            $stats[] = Stat::make('Permintaan Dokumen (Pending)', DocumentRequest::where('user_id', Auth::user()->id)->where('status', 'pending')->count())
                ->icon('heroicon-o-clipboard')
                ->color('primary');
            $stats[] = Stat::make('Permintaan Dokumen (Komplit)', DocumentRequest::where('user_id', Auth::user()->id)->where('status', 'completed')->count())
                ->icon('heroicon-o-clipboard')
                ->color('primary');
        }

        return $stats;
    }
}
