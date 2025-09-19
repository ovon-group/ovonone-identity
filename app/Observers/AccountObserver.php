<?php

namespace App\Observers;

use App\Jobs\SyncAccountWithApplications;
use App\Models\Account;

class AccountObserver
{
    /**
     * Handle the Account "saved" event.
     */
    public function saved(Account $account): void
    {
        SyncAccountWithApplications::dispatch($account);
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        SyncAccountWithApplications::dispatch($account);
    }
}
