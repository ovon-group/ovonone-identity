<?php

namespace Database\Seeders;

use App\Enums\ApplicationEnum;
use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'id' => '01995203-fb59-7008-967c-6864f411199c',
                'owner_type' => null,
                'owner_id' => null,
                'name' => ApplicationEnum::Protego,
                'secret' => '$2y$12$dzw0AYKIBPQG7tAsOTpZNekkC1gmRHheGbJX2i6Z63KotLvaa18t6',
                'provider' => null,
                'redirect_uris' => ['https://dealer.protegoautocare.test/authenticate'],
                'grant_types' => ['authorization_code', 'refresh_token', 'client_credentials'],
                'revoked' => 0,
                'webhook_secret' => 'T4st5wwRbUJjpA99BrBg8W5zBMAVx8zT8hn4+oyvieY=',
                // Client secret = FlZCdI5zxMh0Ey0TnE5oGW8NcYSS5pUkVugCae1G
            ],
            [
                'id' => '019956da-4791-711a-a92d-f19f3af37582',
                'owner_type' => null,
                'owner_id' => null,
                'name' => ApplicationEnum::Wheel2Web,
                'secret' => '$2y$12$ixf6FGxMEC2GSamYe00JMOqB/rMdasiaqEDRQHdseTCOaFUxOB9Tu',
                'provider' => null,
                'redirect_uris' => ['https://wheel2web.test/authenticate'],
                'grant_types' => ['authorization_code', 'refresh_token', 'client_credentials'],
                'revoked' => 0,
                'webhook_secret' => 'WgK9IBRjpibeJbq1Clyv5StoxUAX1VFBn1lKmFx3T+I=',
                // Client secret = Xk57z0maXfgD8ys1kMTU94VcbZ9I5ihNvMuP897B
            ],
        ];

        foreach ($rows as $row) {
            Client::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
