<?php

namespace App\Services;

use App\Enums\ApplicationEnum;
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
        $this->forAllApplications(function (ApplicationEnum $application) use ($user) {
            $this->for($application)
                 ->postRequest(
                     "users/{$user->uuid}",
                     ['user' => $user->applicationPayload($this->application)]
                 );
        }, $user->getApplications());
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

    private function forAllApplications(Closure $callback, ?array $applications = null)
    {
        foreach ($applications ?: ApplicationEnum::cases() as $application) {
            $callback($application);
        }
    }
}
