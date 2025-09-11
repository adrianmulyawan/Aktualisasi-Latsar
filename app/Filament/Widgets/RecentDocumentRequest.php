<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentDocumentRequest extends BaseWidget
{
    protected static ?string $heading = 'Permintaan Dokumen Terbaru';
    protected static ?int $sort = 9;
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 2;

    public function table(Table $table): Table
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Jika user memiliki role 'guest', filter data berdasarkan user yang login
        if ($user && $user->hasRole('guest')) {
            // Query untuk menampilkan permintaan dokumen yang terkait dengan user yang login (baik yang dibuat atau ditugaskan kepadanya)
            return $table
                ->query(
                    \App\Models\DocumentRequest::latest()
                        ->where(function ($query) use ($user) {
                            $query->where('user_id', $user->id)  // Permintaan yang dibuat oleh user
                                ->orWhere('assigned_to', $user->id);  // Permintaan yang ditugaskan kepada user
                        })
                        ->take(5)
                )
                ->columns([
                    Tables\Columns\TextColumn::make('request_document_title')->label('Judul Dokumen')->limit(50),
                    Tables\Columns\TextColumn::make('user.name')->label('Nama Pemohon')->limit(30),
                    Tables\Columns\TextColumn::make('assignedUser.name')->label('Ditugaskan Kepada')->limit(30),
                    Tables\Columns\TextColumn::make('status')->label('Status')
                        ->badge()
                        ->formatStateUsing(fn(string $state): string => match ($state) {
                            'pending'     => 'Pending',
                            'completed'   => 'Komplit',
                            default       => $state,
                        })
                        ->color(fn(string $state): string => match ($state) {
                            'pending'     => 'warning',
                            'completed'   => 'success',
                            default       => 'secondary',
                        }),
                    Tables\Columns\TextColumn::make('created_at')->label('Diajukan Pada')->dateTime('d M Y'),
                ]);
        }

        // Jika user memiliki role selain 'guest' (misalnya 'super_admin', 'user', 'kepala_dinas'),
        // tampilkan semua permintaan dokumen
        return $table
            ->query(
                \App\Models\DocumentRequest::latest()->take(5)  // Ambil 5 permintaan dokumen terbaru
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_document_title')->label('Judul Dokumen')->limit(50),
                Tables\Columns\TextColumn::make('user.name')->label('Nama Pemohon')->limit(30),
                Tables\Columns\TextColumn::make('assignedUser.name')->label('Ditugaskan Kepada')->limit(30),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'     => 'Pending',
                        'completed'   => 'Komplit',
                        default       => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending'     => 'warning',
                        'completed'   => 'success',
                        default       => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan Pada')->dateTime('d M Y'),
            ]);
    }
}
