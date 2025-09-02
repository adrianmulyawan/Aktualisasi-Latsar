<?php

namespace App\Filament\Resources\DocumentRequestResource\Pages;

use App\Filament\Resources\DocumentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDocumentRequest extends CreateRecord
{
    protected static string $resource = DocumentRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
