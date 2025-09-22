<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationEnvironment;
use App\Models\Client;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    protected $applications = [
        [
            'name' => 'Protego',
            'is_active' => true,
            'environments' => [
                [
                    'name' => 'local',
                    'is_active' => true,
                    'url' => 'https://dealer.protegoautocare.test',
                    'client' => [
                        'id' => '01995203-fb59-7008-967c-6864f411199c',
                        'secret' => '$2y$12$dzw0AYKIBPQG7tAsOTpZNekkC1gmRHheGbJX2i6Z63KotLvaa18t6',
                        'redirect_uris' => ['https://dealer.protegoautocare.test/authenticate'],
                        'grant_types' => ['authorization_code', 'refresh_token', 'client_credentials'],
                        'revoked' => 0,
                        // Client secret = FlZCdI5zxMh0Ey0TnE5oGW8NcYSS5pUkVugCae1G
                    ],
                ],
            ],
        ],
        [
            'name' => 'Wheel2web',
            'is_active' => true,
            'environments' => [
                [
                    'name' => 'local',
                    'is_active' => true,
                    'url' => 'https://wheel2web.test',
                    'client' => [
                        'id' => '019956da-4791-711a-a92d-f19f3af37582',
                        'secret' => '$2y$12$ixf6FGxMEC2GSamYe00JMOqB/rMdasiaqEDRQHdseTCOaFUxOB9Tu',
                        'redirect_uris' => ['https://wheel2web.test/authenticate'],
                        'grant_types' => ['authorization_code', 'refresh_token', 'client_credentials'],
                        'revoked' => 0,
                        // Client secret = Xk57z0maXfgD8ys1kMTU94VcbZ9I5ihNvMuP897B
                    ],
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->applications as $applicationData) {
            /** @var Application $application */
            $application = Application::updateOrCreate([
                'name' => $applicationData['name'],
            ], [
                'is_active' => $applicationData['is_active'],
            ]);
            foreach ($applicationData['environments'] as $environmentData) {
                /** @var ApplicationEnvironment $applicationEnvironment */
                $applicationEnvironment = $application->environments()->updateOrCreate([
                    'name' => $environmentData['name'],
                ], [
                    'is_active' => $environmentData['is_active'],
                    'url' => $environmentData['url'],
                ]);
                $applicationEnvironment->clients()->updateOrCreate([
                    'id' => $environmentData['client']['id'],
                    'application_environment_id' => $applicationEnvironment->id,
                    'owner_id' => $applicationEnvironment->getKey(),
                    'owner_type' => $applicationEnvironment->getMorphClass(),
                ], [
                    'name' => $application->name.' '.$applicationEnvironment->name,
                    'secret' => $environmentData['client']['secret'],
                    'redirect_uris' => $environmentData['client']['redirect_uris'],
                    'grant_types' => $environmentData['client']['grant_types'],
                    'revoked' => $environmentData['client']['revoked'],
                ]);
            }
        }
    }
}
