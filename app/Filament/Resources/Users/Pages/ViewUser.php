<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->getFilamentName();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Impersonate::make()
                ->visible(fn (User $user) => Auth::user()->canImpersonate($user))
                ->record($this->getRecord()),
        ];
    }
}
