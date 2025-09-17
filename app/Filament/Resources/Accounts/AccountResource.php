<?php

namespace App\Filament\Resources\Accounts;

use App\Enums\FundType;
use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Accounts\Pages\EditAccount;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\RelationManagers\BreakdownProductsRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\ManufacturerSurchargesRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\PayLaterPlansRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\ServicePlanProductsRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\WarrantyProductsRelationManager;
use App\Filament\Resources\Dealerships\Pages\ManageAccountDealerships;
use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Tapp\FilamentAuditing\RelationManagers\AuditsRelationManager;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                        if ($get('short_name') && $get('short_name') !== Str::shortName($old)) {
                                            return;
                                        }

                                        $set('short_name', Str::shortName($state));
                                    }),
                                TextInput::make('short_name')
                                    ->required()
                                    ->maxLength(15),
                                FileUpload::make('logo_path')
                                    ->directory('logos')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'])
                                    ->imageEditor(),
                            ]),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('short_name')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('short_name')
                          ->sortable()
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'view' => ViewAccount::route('/{record}'),
            'edit' => EditAccount::route('/{record}/edit'),

//            'dealerships' => ManageAccountDealerships::route('/{record}/dealerships'),
        ];
    }
}
