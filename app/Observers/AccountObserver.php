<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\ApplicationService;

class AccountObserver
{
    public function __construct(protected ApplicationService $applicationService) {}

    /**
     * Handle the Account "saved" event.
     */
    public function saved(Account $account): void
    {
        $this->applicationService->pushAccount($account);
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        $this->applicationService->pushAccount($account);
    }
}
