<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentRevision extends BaseWidget
{
    protected static ?string $heading = 'Revisi Dokumen Terbaru';
    protected static ?int $sort = 10;
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 2;

    // Menambahkan pengecekan role pada widget
    public static function canView(): bool
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Cek apakah user memiliki role yang sesuai
        return $user && $user->hasAnyRole(['super_admin', 'user', 'kepala_dinas']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\DocumentRevision::latest()->take(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('document.title')->label('Dokumen')->limit(50),
                Tables\Columns\TextColumn::make('revision_title')->label('Judul Revisi')->limit(30),
                Tables\Columns\TextColumn::make('revision_author')->label('Pembuat')->limit(30),
                Tables\Columns\TextColumn::make('revision_year')->label('Tahun')->limit(30),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'     => 'Draft',
                        'review'    => 'Proses Review',
                        'approved'  => 'Disetujui',
                        'rejected'  => 'Ditolak',
                        default     => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'draft'     => 'primary',  // Badge warna abu-abu untuk draft
                        'review'    => 'warning',    // Badge warna kuning untuk review
                        'approved'  => 'success',    // Badge hijau untuk approved
                        'rejected'  => 'danger',     // Badge merah untuk rejected
                        default     => 'primary',    // Badge biru untuk status lainnya
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat Pada')->dateTime('d M Y'),
            ]);
    }
}
