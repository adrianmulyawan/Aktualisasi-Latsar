<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $oldFiles = $this->record->file;

        // Jika user mengupload file baru
        if (!empty($data['file'])) {
            // Hapus file lama dari storage
            foreach ($oldFiles ?? [] as $oldFile) {
                Storage::disk('public')->delete($oldFile);
            }
        } else {
            // Tidak ada upload baru, gunakan file lama
            $data['file'] = $oldFiles;
        }

        return $data;
    }
}
