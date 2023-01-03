<?php

declare(strict_types=1);

namespace Aeatech\Jwt\Service;

use Aeatech\Commons\Clock;

final class JWTService
{
    private array $config;
    private string $key;
    private string $algorithm;
    private mixed $value;
    private int $expiration;

    public function __construct(?JWTConfigService $configService = null)
    {
        $this->config = $configService ? $configService->resolve() : (new JWTConfigService())->resolve();
        $this->key = $this->config['key'];
        $this->algorithm = 'HS256';
        $this->expiration = strtotime(Clock::now()->asDateTimeString()) + ($this->config['expiration'] * 60);
    }

    public function build(mixed $value): array
    {
        $this->value = $value;
        $headers = $this->headers();
        $payload = $this->payload();
        $parse = $this->parse($headers, $payload);
        $token = $parse['headers'] . '.' . $parse['payload'] . '.' . $parse['signature'];
        return [
            'token' => $token,
            'expires_at' => $this->expiration,
            'type' => 'JWT'
        ];
    }

    public function signature(array $headers, array $payload): string
    {
        return $this->parse($headers, $payload)['signature'];
    }

    private function headers(): array
    {
        return [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];
    }

    private function payload(): array
    {
        return [
            'exp' => $this->expiration,
            'usr' => $this->value
        ];
    }

    private function parse(array $headers, array $payload): array
    {
        $headersEncoded = $this->encode(json_encode($headers));

        $payloadEncoded = $this->encode(json_encode($payload));

        $signature = hash_hmac('SHA256', "$headersEncoded.$payloadEncoded", $this->key, true);
        $signatureEncoded = $this->encode($signature);

        return [
            'headers' => $headersEncoded,
            'payload' => $payloadEncoded,
            'signature' => $signatureEncoded
        ];
    }

    private function encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}