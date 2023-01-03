<?php

declare(strict_types=1);

namespace Aeatech\Jwt\Provider;

final class JWTProvider
{
    public function boot(): void
    {
        copy(__DIR__ . './../../config/jwt-config.php', __DIR__.'./../../../../config/jwt.php');
        echo 'JWT config has been copied for config/ dir.';
    }
}