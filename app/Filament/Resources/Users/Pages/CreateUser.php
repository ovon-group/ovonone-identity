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

    protected bool $alsoCreateSalesPerson = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->alsoCreateSalesPerson = (bool) ($data['create_sales_person'] ?? false);
        unset($data['create_sales_person']);

        $data['account_id'] = auth()->user()->account_id;
        $data['is_internal'] = Auth::user()->isViewingAllRecords();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $newUser */
        $newUser = $this->getRecord();

        if ($newUser->isDealer()) {
            $newUser->accounts()->attach(Auth::user()->account_id);
            if ($this->alsoCreateSalesPerson) {
                SalesPerson::create([
                    'account_id' => $newUser->account_id,
                    'user_id' => $newUser->id,
                    'name' => $newUser->name,
                ]);
            }
        }

        event(new Registered($newUser));
    }
}
