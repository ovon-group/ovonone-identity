<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema(function (?User $record, Get $get) {
                        $isInternal = (bool) ($record ? $record->is_internal : $get('is_internal'));

                        return [
                            Toggle::make('is_internal')
                                ->visibleOn('create')
                                ->columnSpanFull()
                                ->live(),
                            TextInput::make('name')
                                ->required()
                                ->maxLength(25),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                            Select::make('accounts')
                                ->live()
                                ->multiple()
                                ->hidden($isInternal)
                                ->relationship(
                                    name: 'accounts',
                                    titleAttribute: 'name',
                                )
                                ->preload()
                                ->required()
                                ->minItems(1),
                            Select::make('roles')
                                ->multiple()
                                ->relationship(
                                    name: 'roles',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (?User $record, Get $get, $query) => $query
                                        ->when(
                                            $isInternal,
                                            fn ($q) => $q->where('is_internal', true),
                                            fn ($q) => $q->whereIn('app', Account::find($get('accounts'))
                                                ->pluck('applications')
                                                ->flatten()
                                                ->unique()
                                            ),
                                        )
                                )
                                ->getOptionLabelFromRecordUsing(fn (Role $record) => $record->getFilamentName())
                                ->preload(),
                        ];
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('accounts.name')
                    ->listWithLineBreaks()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->listWithLineBreaks()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
