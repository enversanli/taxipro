<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    use AdminRoleTrait;

    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Companies';
    protected static ?string $pluralModelLabel = 'Companies';
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('owner_id')
                    ->relationship('owner', 'first_name')
                    ->searchable()
                    ->label('Owner')
                    ->required(),

                TextInput::make('name')
                    ->label('Company Name')
                    ->required(),

                TextInput::make('address')
                    ->required(),

                TextInput::make('phone')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Company')->searchable()->sortable(),
                TextColumn::make('owner.name')->label('Owner')->sortable()->searchable(),
                TextColumn::make('phone')->sortable(),
                TextColumn::make('email')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('Created')->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
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
        return [
            //
        ];
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
