<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentRequestResource\Pages;
use App\Filament\Resources\DocumentRequestResource\RelationManagers;
use App\Models\Document;
use App\Models\DocumentRequest;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentRequestResource extends Resource
{
    protected static ?string $model = DocumentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Permintaan Dokumen';
    protected static ?string $label = 'Data Permintaan Dokumen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('request_document_title')
                    ->label('Judul Dokumen yang Diminta')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Select::make('is_internal_document')
                    ->label('Jenis Dokumen (Eksternal/Internal)')
                    ->options([
                        0 => 'Eksternal (meminta dibuatkan dokumen baru)',
                        1 => 'Internal (meminta akses dokumen yang sudah ada)',
                    ])
                    ->required()
                    ->reactive()
                    ->columnSpanFull(),
                Select::make('existing_document_id')
                    ->label('Pilih Jenis Dokumen (Internal)')
                    ->relationship('document', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(fn(Get $get) => (int) $get('is_internal_document') === 1)
                    ->required(fn(Get $get) => (int) $get('is_internal_document') === 1)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('URL Dokumen (Eksternal)')
                    ->maxLength(255)
                    ->visible(fn(Get $get) => (int) $get('is_internal_document') === 0)
                    // ->required(fn(Get $get) => (int) $get('is_internal_document') === 0)
                    ->columnSpanFull(),
                FileUpload::make('file')
                    ->label('Upload File Dokumen (Eksternal)')
                    ->directory('document_requests')
                    ->acceptedFileTypes([
                        'application/pdf', // .pdf
                        'application/msword', // .doc
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.ms-excel', // .xls
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    ])
                    ->helperText('Format yang diperbolehkan: pdf, doc, docx, xlsx. Ukuran maksimal: 2MB.')
                    ->maxSize(10240) // 10 MB
                    ->multiple()
                    ->downloadable()
                    ->openable()
                    ->maxFiles(5)
                    ->visible(fn(Get $get) => (int) $get('is_internal_document') === 0)
                    // ->required(fn(Get $get) => (int) $get('is_internal_document') === 0)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Catatan Tambahan')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('author')
                    ->label('Penulis')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->default(Auth::user()->name),
                DatePicker::make('date')
                    ->label('Tanggal Dokumen')
                    ->date()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status Permintaan')
                    ->options([
                        'pending' => 'Pending',
                        // 'in_progress' => 'In Progress',
                        'completed' => 'Komplit',
                        // 'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn($livewire) => $livewire instanceof Pages\EditDocumentRequest),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_document_title')
                    ->label('Judul Dokumen yang Diminta')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('assignedUser.name')
                    ->label('Ditugaskan Kepada')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('is_internal_document')
                    ->label('Jenis Dokumen')
                    ->formatStateUsing(fn($state) => $state ? 'Internal' : 'Eksternal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status Permintaan')
                    ->searchable()
                    ->sortable()
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_internal_document')
                    ->label('Jenis Dokumen')
                    ->options([
                        0 => 'Eksternal',
                        1 => 'Internal',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Permintaan')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('user')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentRequests::route('/'),
            'create' => Pages\CreateDocumentRequest::route('/create'),
            'edit' => Pages\EditDocumentRequest::route('/{record}/edit'),
        ];
    }

    // ini buat membatasi data yang ditampilkan di list
    // super_admin & user boleh lihat semua
    // guest hanya bisa lihat yang ditugaskan ke dia
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        // super_admin boleh lihat semua
        if ($user?->hasRole('super_admin') || $user?->hasRole('user')) {
            return $query;
        }

        if ($user->hasRole('guest')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        // selain itu: hanya yang ditugaskan ke user login
        return $query->where('assigned_to', $user?->id ?? 0);
    }

    public static function canView(Model $record): bool
    {
        // return Auth::user()->hasRole('super_admin') || (Auth::user()->hasRole('user') && ($record->user_id === Auth::user()->id || $record->assigned_to === Auth::user()->id));
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user') || (Auth::user()->hasRole('guest') && ($record->user_id === Auth::user()->id || $record->assigned_to === Auth::user()->id));
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    }
}
