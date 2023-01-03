<?php

declare(strict_types=1);

namespace App\Provider;

final class KernelProvider
{
    private static array $providers = [
        \Aeatech\Jwt\Provider\JWTProvider::class
    ];

    public static function boot(): void
    {

    }

    public static function publish(): void
    {
        foreach (self::$providers as $provider) {
            (new $provider())->boot();
        }
    }
}
