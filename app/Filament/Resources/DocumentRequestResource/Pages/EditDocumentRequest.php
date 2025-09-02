<?php

namespace App\Filament\Resources\DocumentRequestResource\Pages;

use App\Filament\Resources\DocumentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocumentRequest extends EditRecord
{
    protected static string $resource = DocumentRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ambil file lama dari record
        $oldFiles = $this->record->file;

        // Periksa apakah user memilih file baru
        $isNewUpload = !empty($data['file']) && $data['file'] !== $oldFiles;

        if ($isNewUpload) {
            // Hapus file lama dari storage
            foreach ($oldFiles ?? [] as $oldFile) {
                Storage::disk('public')->delete($oldFile);
            }
            // Biarkan file baru disimpan
        } else {
            // User tidak upload file baru, simpan file lama
            $data['file'] = $oldFiles;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
