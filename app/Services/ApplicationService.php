<?php

namespace App\Services;

use App\Enums\ApplicationEnum;
use App\Models\Account;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Http;

class ApplicationService
{
    protected ?ApplicationEnum $application = null;

    public function for(ApplicationEnum $application)
    {
        $this->application = $application;

        return $this;
    }

    public function pushUser(User $user)
    {
        $this->forAllApplications(
            $user->getApplications(),
            function (ApplicationEnum $application) use ($user) {
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
            $account->getApplications(),
            function (ApplicationEnum $application) use ($account) {
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
        $response = Http::baseUrl($this->application->getUrl().'/api')
            ->acceptJson()
            ->put($url, $payload);

        if ($response->failed()) {
            dd($response->getBody()->getContents());
        }
    }

    private function forAllApplications($applications, Closure $callback)
    {
        foreach ($applications as $application) {
            $callback($application);
        }
    }
}
