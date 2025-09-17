<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

//    public static function getEloquentQuery(): Builder
//    {
//        return parent::getEloquentQuery()->when(
//            Auth::user()->isViewingAllRecords(),
//            fn ($query) => $query->admin(),
//            fn ($query) => $query->dealer()->whereHas('accounts', fn ($query) => $query->where('account_id', Auth::user()->account_id))
//        );
//    }

    public static function canAccess(): bool
    {
//        if (Auth::user()->isViewingAllRecords() && ! Auth::user()->hasPermissionTo('admin-users.manage')) {
//            return false;
//        }

        return parent::canAccess();
    }

//    public static function getNavigationLabel(): string
//    {
//        return Auth::user()->isViewingAllRecords() ? 'Admin Users' : 'Users';
//    }

    public static function getBreadcrumb(): string
    {
        return static::getNavigationLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(25),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('role_id')
                            ->multiple()
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
//                                modifyQueryUsing: fn ($query) => $query->where('is_internal', Auth::user()->isViewingAllRecords())
                            )
                            ->preload()
                            ->required()
                            ->rules([
//                                fn (): Closure => function (string $attribute, $value, Closure $fail) {
//                                    $allowedRoles = Role::where('is_internal', Auth::user()->isViewingAllRecords())->pluck('id');
//
//                                    if (! collect($value)->every(fn (string $roleId) => $allowedRoles->contains($roleId))) {
//                                        $fail('The :attribute field contains an invalid role.');
//                                    }
//                                },
                                fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                    if (count($value) === 0) {
                                        $fail('The :attribute field must have at least one role.');
                                    }
                                },
                            ]),
                    ]),
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
        return [
//            AuditsRelationManager::class,
        ];
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
