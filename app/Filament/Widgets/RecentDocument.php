<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDocument extends BaseWidget
{
    protected static ?string $heading = 'Dokumen Terbaru';
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Document::latest()->take(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul Dokumen')->limit(50),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori'),
                Tables\Columns\TextColumn::make('year')->label('Tahun'),
                Tables\Columns\TextColumn::make('author')->label('Penulis')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y'),
            ]);
    }
}
