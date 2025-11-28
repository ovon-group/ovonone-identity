<?php

namespace App\Console\Commands;

use App\Enums\ApplicationEnum;
use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class CreateClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-clients {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a client for each application';

    /**
     * Execute the console command.
     */
    public function handle(ClientRepository $clientRepository): void
    {
        foreach (ApplicationEnum::cases() as $application) {
            $existingClient = Passport::client()->newQuery()->where('name', $application->value)->exists();

            if ($existingClient && ! $this->option('force')) {
                $this->components->warn("Existing client found for {$application->getLabel()}, no action taken.");

                continue;
            }

            Passport::client()->newQuery()->where('name', $application->value)->delete();

            $client = $clientRepository->createAuthorizationCodeGrantClient(
                name: $application->value,
                redirectUris: [$application->getUrl()],
            );
            $client->grant_types = [
                ...$client->grant_types,
                'client_credentials',
            ];
            $client->webhook_secret = bin2hex(random_bytes(20));
            $client->save();

            $this->components->info('New client created successfully.');

            $this->components->twoColumnDetail('Client name', $client->name);
            $this->components->twoColumnDetail('Client ID', $client->getKey());

            if ($client->confidential()) {
                $this->components->twoColumnDetail('Client Secret', $client->plainSecret);
                $this->components->twoColumnDetail('Webhook Secret', $client->webhook_secret);
                $this->components->warn('The client secret will not be shown again, so don\'t lose it!');
            }
        }
    }
}
