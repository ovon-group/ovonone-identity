<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
