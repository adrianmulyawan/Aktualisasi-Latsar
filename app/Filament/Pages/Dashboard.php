<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->placeholder('Pilih tanggal mulai')
                    ->columnSpanFull(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->placeholder('Pilih tanggal selesai')
                    ->columnSpanFull(),
            ]);
    }
}
