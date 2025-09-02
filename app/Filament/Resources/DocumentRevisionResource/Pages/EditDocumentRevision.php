<?php

namespace App\Filament\Resources\DocumentRevisionResource\Pages;

use App\Filament\Resources\DocumentRevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocumentRevision extends EditRecord
{
    protected static string $resource = DocumentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // logic ketika user ada input file baru
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ambil file lama dari record
        $oldFiles = $this->record->revision_file;

        // Periksa apakah user memilih file baru
        $isNewUpload = !empty($data['revision_file']) && $data['revision_file'] !== $oldFiles;

        if ($isNewUpload) {
            // Hapus file lama dari storage
            foreach ($oldFiles ?? [] as $oldFile) {
                Storage::disk('public')->delete($oldFile);
            }
            // Biarkan file baru disimpan
        } else {
            // User tidak upload file baru, simpan file lama
            $data['revision_file'] = $oldFiles;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
