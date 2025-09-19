<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\SalesPerson;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        /** @var User $newUser */
        $newUser = $this->getRecord();

        event(new Registered($newUser));
    }
}
