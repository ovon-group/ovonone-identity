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
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        $this->applicationService->pushUser($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->applicationService->pushUser($user);
    }
}
