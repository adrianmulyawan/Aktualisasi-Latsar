<?php

namespace App\Filament\Resources\DocumentRevisionResource\Pages;

use App\Filament\Resources\DocumentRevisionResource;
use App\Models\DocumentRevision;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListDocumentRevisions extends ListRecords
{
    protected static string $resource = DocumentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'draft', 'review', 'approved', 'rejected'
            Tab::make('Semua'),
            Tab::make('Draft')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft'))
                ->badge(DocumentRevision::query()->where('status', 'draft')->count())
                ->icon('heroicon-m-clipboard'),
            Tab::make('Proses Review')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'review'))
                ->badge(DocumentRevision::query()->where('status', 'review')->count())
                ->icon('heroicon-m-clock'),
            Tab::make('Diterima')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved'))
                ->badge(DocumentRevision::query()->where('status', 'approved')->count())
                ->icon('heroicon-m-hand-thumb-up'),
            Tab::make('Ditolak')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected'))
                ->badge(DocumentRevision::query()->where('status', 'rejected')->count())
                ->icon('heroicon-m-hand-thumb-down'),
        ];
    }
}
