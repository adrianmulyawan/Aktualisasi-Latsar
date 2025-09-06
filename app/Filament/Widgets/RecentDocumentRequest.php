<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDocumentRequest extends BaseWidget
{
    protected static ?string $heading = 'Permintaan Dokumen Terbaru';
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\DocumentRequest::latest()->take(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_document_title')->label('Judul Dokumen')->limit(50),
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pemohon')->limit(30),
                Tables\Columns\TextColumn::make('assignedUser.name')->label('Ditugaskan Kepada')->limit(30),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'     => 'Pending',
                        // 'in_progress' => 'Sedang Diproses',
                        'completed'   => 'Komplit',
                        // 'rejected'    => 'Ditolak',
                        default       => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending'     => 'warning',
                        // 'in_progress' => 'primary',
                        'completed'   => 'success',
                        // 'rejected'    => 'danger',
                        default       => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan Pada')->dateTime('d M Y'),
            ]);
    }
}
