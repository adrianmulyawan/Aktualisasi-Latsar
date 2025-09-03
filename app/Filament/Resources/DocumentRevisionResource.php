<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentRevisionResource\Pages;
use App\Filament\Resources\DocumentRevisionResource\RelationManagers;
use App\Models\DocumentRevision;
use Dom\Text;
use DragonCode\PrettyArray\Services\File;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentRevisionResource extends Resource
{
    protected static ?string $model = DocumentRevision::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationLabel = 'Revisi Dokumen';
    protected static ?string $label = 'Data Revisi Dokumen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('document_id')
                    ->label('Dokumen')
                    ->relationship('document', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('revision_title')
                    ->label('Judul Revisi')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        $set('revision_slug', \Illuminate\Support\Str::slug($state));
                    }),
                TextInput::make('revision_slug')
                    ->label('Slug Revisi')
                    ->required()
                    ->maxLength(255)
                    ->unique(DocumentRevision::class, 'revision_slug', ignoreRecord: true)
                    ->columnSpanFull(),
                RichEditor::make('revision_description')
                    ->label('Deskripsi Revisi')
                    ->columnSpanFull(),
                FileUpload::make('revision_file')
                    ->label('File Revisi')
                    ->columnSpanFull()
                    ->directory('document_revisions')
                    ->acceptedFileTypes([
                        'application/pdf', // .pdf
                        'application/msword', // .doc
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.ms-excel', // .xls
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    ])
                    // ->visibility('private')
                    ->maxFiles(5)
                    ->multiple()
                    // ->preserveFilenames()
                    ->downloadable()
                    ->openable()
                    ->helperText('Format yang diperbolehkan: pdf, doc, docx, xlsx. Ukuran maksimal: 2MB.')
                    ->columnSpanFull(),
                TextInput::make('revision_url')
                    ->label('URL Revisi')
                    ->url()
                    ->columnSpanFull(),
                TextInput::make('revision_author')
                    ->label('Penulis Revisi')
                    ->maxLength(255)
                    ->default(Auth::user()->name)
                    ->columnSpanFull(),
                TextInput::make('revision_year')
                    ->label('Tahun Revisi')
                    ->maxLength(4)
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(3000)
                    ->columnSpanFull(),
                DatePicker::make('revision_date')
                    ->label('Tanggal Revisi')
                    ->date()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Proses Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('draft')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn($livewire) => $livewire instanceof Pages\EditDocumentRevision),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document.title')
                    ->label('Judul Dokumen')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('revision_title')
                    ->label('Judul Revisi')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('revision_author')
                    ->label('Penulis Revisi')
                    ->searchable()
                    ->sortable()
                    ->limit(20),
                TextColumn::make('revision_year')
                    ->label('Tahun Revisi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('revision_date')
                    ->label('Tanggal Revisi')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'     => 'Draft',
                        'review'    => 'Proses Review',
                        'approved'  => 'Disetujui',
                        'rejected'  => 'Ditolak',
                        default     => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'draft'     => 'primary',  // Badge warna abu-abu untuk draft
                        'review'    => 'warning',    // Badge warna kuning untuk review
                        'approved'  => 'success',    // Badge hijau untuk approved
                        'rejected'  => 'danger',     // Badge merah untuk rejected
                        default     => 'primary',    // Badge biru untuk status lainnya
                    }),
            ])
            ->filters([
                SelectFilter::make('document')
                    ->label('Dokumen')
                    ->relationship('document', 'title')
                    ->multiple()
                    ->preload(true),
                SelectFilter::make('revision_year')
                    ->label('Tahun Revisi')
                    ->options(function () {
                        return DocumentRevision::query()
                            ->selectRaw('DISTINCT revision_year')
                            ->orderBy('revision_year', 'desc')
                            ->pluck('revision_year', 'revision_year')
                            ->toArray();
                    })->placeholder('Semua Tahun'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Proses Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->placeholder('Semua Status'),
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
            'index' => Pages\ListDocumentRevisions::route('/'),
            'create' => Pages\CreateDocumentRevision::route('/create'),
            'edit' => Pages\EditDocumentRevision::route('/{record}/edit'),
        ];
    }

    // public static function canViewAny(): bool
    // {
    //     return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    // }

    public static function canView(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    }

    public static function canEdit(Model $record): bool
    {
        // return Auth::user()->hasRole('super_admin') || (Auth::user()->hasRole('user') && $record->user_id === Auth::user()->id);
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin') || (Auth::user()->hasRole('user') && $record->user_id === Auth::user()->id);
    }
}
