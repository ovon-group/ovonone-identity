<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\ApplicationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAccountWithApplications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Account $account) {}

    /**
     * Execute the job.
     */
    public function handle(ApplicationService $applicationService): void
    {
        $applicationService->pushAccount($this->account);
    }
}
