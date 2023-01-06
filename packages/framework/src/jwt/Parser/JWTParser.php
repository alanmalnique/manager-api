<?php

declare(strict_types=1);

namespace Aeatech\Jwt\Parser;

use Aeatech\Jwt\Exception\JWTExpiredException;
use Aeatech\Jwt\Exception\JWTInvalidFormatException;
use Aeatech\Jwt\Exception\JWTInvalidSignatureException;
use Aeatech\Jwt\Service\JWTConfigService;
use Aeatech\Jwt\Service\JWTService;
use Aeatech\Commons\Clock;

final class JWTParser
{
    private array $header;
    private array $payload;
    private string $signature;
    private ?JWTConfigService $config;

    public function __construct(?JWTConfigService $configService = null)
    {
        $this->config = $configService ? : null;
    }

    /**
     * @throws JWTInvalidFormatException
     * @throws JWTExpiredException
     * @throws JWTInvalidSignatureException
     */
    public function parse(string $token): string
    {
        $token = $this->normalize($token);
        $tokenParts = explode(".", $token);
        if (count($tokenParts) < 3) {
            throw new JWTInvalidFormatException('JWT invalid format.', 401);
        }
        $this->header = json_decode(base64_decode($tokenParts[0]), true);
        $this->payload = json_decode(base64_decode($tokenParts[1]), true);
        $this->signature = $tokenParts[2];

        $this->validateExpiration();
        $this->validateSignature();

        return $this->payload['usr'];
    }

    private function validateExpiration(): void
    {
        $expiration = $this->payload['exp'];
        $timestamp = strtotime(Clock::now()->asDateTimeString());
        if (($expiration - $timestamp) < 0) {
            throw new JWTExpiredException('JWT token expired.', 401);
        }
    }

    private function validateSignature(): void
    {
        $signature = (new JWTService($this->config))->signature($this->header, $this->payload);
        if ($signature !== $this->signature) {
            throw new JWTInvalidSignatureException('JWT invalid signature.', 401);
        }
    }

    private function normalize(string $token): string
    {
        return str_replace(["bearer ", "Bearer "], "", $token);
    }
}