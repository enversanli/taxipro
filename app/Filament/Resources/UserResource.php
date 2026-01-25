<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Company;
use App\Models\User;
use App\Traits\AdminRoleTrait;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    use AdminRoleTrait;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users'; // Plural icon fits better
    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getNavigationLabel(): string
    {
        return __('common.users');
    }

    public static function getNavigationGroup(): string
    {
        return __('common.management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3) // Modern 3-column layout
            ->schema([

                // --- LEFT: IDENTITY & SECURITY (2/3) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Benutzerprofil')
                            ->description('Persönliche Daten und Anmeldeinformationen')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('first_name')
                                        ->label(__('common.first_name'))
                                        ->required()
                                        ->prefixIcon('heroicon-m-user'),

                                    TextInput::make('last_name')
                                        ->label(__('common.last_name'))
                                        ->required()
                                        ->prefixIcon('heroicon-m-user'),
                                ]),

                                TextInput::make('email')
                                    ->label(__('common.email'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->columnSpanFull(),

                                TextInput::make('password')
                                    ->password()
                                    ->label(__('common.password'))
                                    ->revealable()
                                    ->prefixIcon('heroicon-m-key')
                                    ->helperText(fn ($livewire) => $livewire instanceof Pages\EditUser
                                        ? 'Nur ausfüllen, wenn das Passwort geändert werden soll.'
                                        : '')
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // --- RIGHT: ACCESS CONTROL (1/3) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Zugriffsrechte')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Select::make('role')
                                    ->label(__('common.role'))
                                    ->options([
                                        'admin' => __('common.admin'),
                                        'partner' => __('common.partner'),
                                        'driver' => __('common.driver'),
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-identification'),

                                Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->options(Company::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-building-office')
                                    ->visible(fn() => auth()->user()->role === 'admin'), // Safety check
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Combined User Info Column
                TextColumn::make('full_name')
                    ->label(__('Benutzer'))
                    ->state(fn (User $record) => "{$record->first_name} {$record->last_name}")
                    ->description(fn (User $record) => $record->email)
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(['first_name'])
                    ->icon('heroicon-m-user-circle'),

                TextColumn::make('role')
                    ->label(__('common.role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',   // Purple/Red for high privilege
                        'partner' => 'info',   // Blue for partners
                        'driver' => 'success', // Green/Gray for standard users
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'admin' => 'heroicon-m-shield-check',
                        'partner' => 'heroicon-m-briefcase',
                        'driver' => 'heroicon-m-truck',
                        default => 'heroicon-m-user',
                    }),

                TextColumn::make('company.name')
                    ->label(__('common.company'))
                    ->badge()
                    ->color('gray')
                    ->visible(fn() => auth()->user()->role === 'admin')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('common.registered'))
                    ->dateTime('d.m.Y')
                    ->color('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(), // Modern SlideOver
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
