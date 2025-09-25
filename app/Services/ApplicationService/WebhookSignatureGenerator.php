<?php

namespace App\Services\ApplicationService;

class WebhookSignatureGenerator
{
    public function generate(array $params, string $secret): string
    {
        ksort($params);
        $payload = json_encode($params);

        return base64_encode(hash_hmac('sha256', $payload, $secret, true));
    }

    public function validate(string $signature, array $params, string $secret): bool
    {
        return hash_equals($this->generate($params, $secret), $signature);
    }
}
