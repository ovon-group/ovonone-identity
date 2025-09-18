<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ApplicationService;

class UserObserver
{
    public function __construct(protected ApplicationService $applicationService)
    {
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->applicationService->pushUser($user);
    }
}
