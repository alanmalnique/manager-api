<?php

declare(strict_types=1);

namespace Aeatech\Jwt;

use Aeatech\Jwt\Exception\JWTExpiredException;
use Aeatech\Jwt\Exception\JWTInvalidFormatException;
use Aeatech\Jwt\Exception\JWTInvalidSignatureException;
use Aeatech\Jwt\Parser\JWTParser;
use Aeatech\Jwt\Service\JWTConfigService;
use Aeatech\Jwt\Service\JWTService;

final class JWT
{
    private static string $token;
    private static JWTConfigService $configService;

    public static function generate(mixed $value): array
    {
        $configService = self::$configService ?: null;
        $service = (new JWTService($configService))->build($value);
        self::$token = $service['token'];
        return $service;
    }

    /**
     * @throws JWTInvalidFormatException
     * @throws JWTInvalidSignatureException
     * @throws JWTExpiredException
     */
    public static function validate(string $token): string
    {
        $configService = self::$configService ?: null;
        return (new JWTParser($configService))->parse($token);
    }

    public static function token(): string
    {
        return self::$token;
    }

    public static function config(JWTConfigService $configService): void
    {
        self::$configService = $configService;
    }
}