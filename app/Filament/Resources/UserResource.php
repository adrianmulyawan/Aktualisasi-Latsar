<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $label = 'Data Pengguna';
    protected static ?string $navigationGroup = 'Daftar Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->required(fn(Forms\Get $get) => $get('id') === null)
                    ->minLength(8)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->maxLength(255)
                    ->dehydrated(fn($state) => !empty($state))
                    ->revealable(true)
                    ->columnSpanFull()
                    ->visible(fn(string $operation): bool => $operation === 'create'),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Terverifikasi Pada')
                    ->required(fn(Forms\Get $get) => $get('id') === null)
                    ->columnSpanFull()
                    ->default(now()),
                Select::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(true)
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // Rule for specifying who can access this resource
    public static function canCreate(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    public static function canView(Model $record): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah Pengguna Terdaftar';
    }
}
