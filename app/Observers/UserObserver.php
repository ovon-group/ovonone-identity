<?php

namespace App\Observers;

use App\Jobs\SyncUserWithApplications;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        SyncUserWithApplications::dispatch($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        SyncUserWithApplications::dispatch($user);
    }
}
