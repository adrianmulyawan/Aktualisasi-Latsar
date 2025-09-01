<?php

namespace App\Filament\Resources\DocumentRevisionResource\Pages;

use App\Filament\Resources\DocumentRevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentRevision extends EditRecord
{
    protected static string $resource = DocumentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
