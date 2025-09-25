<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ApplicationService\ApplicationApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncUserWithApplications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(ApplicationApiService $applicationService): void
    {
        $applicationService->pushUser($this->user);
    }
}
