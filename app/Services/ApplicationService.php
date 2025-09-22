<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Application;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Http;

class ApplicationService
{
    protected ?Application $application = null;

    public function for(Application $application)
    {
        $this->application = $application;

        return $this;
    }

    public function pushUser(User $user)
    {
        $this->forAllApplications(
            $user->getApplications(),
            function (Application $application) use ($user) {
                $this->for($application)
                    ->postRequest(
                        'users',
                        ['user' => $user->applicationPayload($this->application)]
                    );
            }
        );
    }

    public function pushAccount(Account $account)
    {
        $this->forAllApplications(
            $account->applications,
            function (Application $application) use ($account) {
                $this->for($application)
                    ->postRequest(
                        'accounts',
                        ['account' => $account->applicationPayload()]
                    );
            }
        );
    }

    private function postRequest(string $url, array $payload)
    {
        // Get the production URL for this application
        $baseUrl = $this->application->getProductionUrl();

        if (!$baseUrl) {
            throw new \Exception("No production URL found for application: {$this->application->name}");
        }

        $response = Http::baseUrl($baseUrl.'/api')
            ->acceptJson()
            ->put($url, $payload);

        if ($response->failed()) {
            dd($response->getBody()->getContents());
        }
    }

    private function forAllApplications($applications, Closure $callback)
    {
        foreach ($applications as $application) {
            try {
                $callback($application);
            } catch (\Throwable $throwable) {
                report($throwable);
            }
        }
    }
}
