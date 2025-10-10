<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Traits\AdminRoleTrait;
use Filament\Forms;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    use AdminRoleTrait;

    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    public static function getNavigationLabel(): string
    {
        return __('common.companies');
    }

    public static function getPluralModelLabel(): string
    {
        return __('common.companies');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('owner_id')
                    ->relationship('owner', 'first_name')
                    ->searchable()
                    ->label(__('common.owner'))
                    ->required(),

                TextInput::make('name')
                    ->label(__('common.company_name'))
                    ->required(),

                TextInput::make('address')
                    ->label(__('common.address'))
                    ->required(),

                TextInput::make('phone')
                    ->label(__('common.phone'))
                    ->required(),

                TextInput::make('email')
                    ->label(__('common.email'))
                    ->email()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('common.company'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label(__('common.owner'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('common.phone'))
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('common.email'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Add filters later if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
