<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->visible(fn (User $user) => Auth::user()->canImpersonate($user))
                ->record($this->getRecord()),
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return Auth::user()->isViewingAllRecords() ? 'Edit Admin User' : 'Edit User';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['account_id'] = auth()->user()->account_id;

        return $data;
    }

    public function getRelationManagers(): array
    {
        $relations = parent::getRelationManagers();
        if (! Auth::user()->isViewingAllRecords()) {
            $relations[] = AccountsRelationManager::class;
        }

        return $relations;
    }
}
