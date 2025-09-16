<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    protected function canAttach(): bool
    {
        return Auth::user()->isViewingAllRecords() === false;
    }

    protected function canDetachAny(): bool
    {
        return Auth::user()->isViewingAllRecords() === false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make(),
            ])
            ->recordActions([
                DetachAction::make()->requiresConfirmation(),
            ]);
    }
}
