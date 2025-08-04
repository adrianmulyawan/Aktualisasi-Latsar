<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Dom\Text;
use DragonCode\PrettyArray\Services\File;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel = 'Dokumen';
    protected static ?string $label = 'Data Dokumen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul Dokumen')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $set('slug', Str::slug($state));
                        // $set('author', auth()->user()->name);
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Document::class, 'slug', ignoreRecord: true)
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    // ->searchable()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->rows(10)
                    ->cols(20),
                TextInput::make('year')
                    ->label('Tahun')
                    ->required()
                    ->maxLength(4)
                    ->numeric()
                    ->columnSpanFull()
                    ->minValue(2000)
                    ->maxValue(3000),
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->columnSpanFull(),
                FileUpload::make('file')
                    ->label('File Dokumen')
                    ->directory('documents')
                    ->acceptedFileTypes([
                        'application/pdf', // .pdf
                        'application/msword', // .doc
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.ms-excel', // .xls
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                    ])
                    ->maxSize(10240) // 10 MB
                    ->multiple()
                    ->downloadable()
                    ->openable()
                    // ->enableDownload()
                    // ->enableOpen()
                    ->maxFiles(5)
                    ->columnSpanFull(),
                TextInput::make('author')
                    ->label('Uploader')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->default(Auth::user()->name),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Dokumen')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(true),
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(Document::select('year')->distinct()->pluck('year', 'year'))
                    ->multiple()
                    ->preload(true),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('user');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin') || (Auth::user()->hasRole('user') && $record->user_id === Auth::user()->id);
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin') || (Auth::user()->hasRole('user') && $record->user_id === Auth::user()->id);
    }
}
