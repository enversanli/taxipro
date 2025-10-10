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
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class UserResource extends Resource
{
    use AdminRoleTrait;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

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
            ->schema([
                Select::make('company_id')
                    ->label(__('common.company'))
                    ->options(Company::all()->pluck('name', 'id'))
                    ->searchable(),

                Select::make('role')
                    ->label(__('common.role'))
                    ->options([
                        'admin' => __('common.admin'),
                        'partner' => __('common.partner'),
                        'driver' => __('common.driver'),
                    ]),

                TextInput::make('first_name')
                    ->label(__('common.first_name'))
                    ->required(),

                TextInput::make('last_name')
                    ->label(__('common.last_name'))
                    ->required(),

                TextInput::make('email')
                    ->label(__('common.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->label(__('common.password'))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('common.first_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label(__('common.last_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('common.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label(__('common.role'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('common.registered'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('common.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('common.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('common.delete_selected')),
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
