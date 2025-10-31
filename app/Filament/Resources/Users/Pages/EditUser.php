<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Edit '.$this->getRecord()->getFilamentName();
    }

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->visible(fn (User $user) => Auth::user()->canImpersonate($user))
                ->record($this->getRecord()),
            RestoreAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
