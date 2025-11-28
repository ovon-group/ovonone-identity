<?php

namespace App\Services\ApplicationService;

use App\Enums\ApplicationEnum;
use App\Models\Account;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Http;

class ApplicationApiService
{
    protected ?ApplicationEnum $application = null;

    public function __construct(protected WebhookSignatureGenerator $webhookHasher) {}

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
                    ->sendPutRequest(
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
                    ->sendPutRequest(
                        'accounts',
                        ['account' => $account->applicationPayload()]
                    );
            }
        );
    }

    private function sendPutRequest(string $url, array $payload)
    {
        $signature = $this->webhookHasher->generate(
            params: $payload,
            secret: $this->application->getClient()->webhook_secret,
        );

        Http::baseUrl($this->application->getUrl().'/api')
            ->withHeaders(['X-Signature' => $signature])
            ->acceptJson()
            ->throw()
            ->put($url, $payload);
    }

    private function forAllApplications($applications, Closure $callback)
    {
        foreach ($applications as $application) {
            try {
                $callback($application);
            } catch (\Throwable $throwable) {
                if (app()->isLocal()) {
                    throw $throwable;
                }
                report($throwable);
            }
        }
    }
}
