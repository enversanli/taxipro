<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Traits\AdminRoleTrait;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2'; // More modern building icon
    protected static ?string $recordTitleAttribute = 'name';

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
            ->columns(3) // Standard modern layout
            ->schema([

                // --- LEFT: COMPANY IDENTITY (2/3) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Firmendaten')
                            ->description('Allgemeine Informationen zum Unternehmen')
                            ->icon('heroicon-m-building-office')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('common.company_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-building-library')
                                    ->columnSpanFull(),

                                TextInput::make('address')
                                    ->label(__('common.address'))
                                    ->required()
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // --- RIGHT: CONTACT & OWNER (1/3) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Kontakt & Inhaber')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Select::make('owner_id')
                                    ->label(__('common.owner'))
                                    ->relationship('owner', 'first_name') // Ensure this matches your User model name field
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-m-user'),

                                TextInput::make('phone')
                                    ->label(__('common.phone'))
                                    ->tel()
                                    ->required()
                                    ->prefixIcon('heroicon-m-phone'),

                                TextInput::make('email')
                                    ->label(__('common.email'))
                                    ->email()
                                    ->nullable()
                                    ->prefixIcon('heroicon-m-envelope'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('common.company'))
                    ->weight('bold')
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label(__('common.owner'))
                    ->formatStateUsing(fn ($record) => $record->owner ? "{$record->owner->first_name} {$record->owner->last_name}" : '-')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('common.phone'))
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('common.email'))
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->color('gray')
                    ->toggleable(), // Allow hiding if table is too wide

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d.m.Y')
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(), // SlideOver for cleaner UX
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
