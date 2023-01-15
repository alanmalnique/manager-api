<?php

declare(strict_types=1);

namespace Aeatech\Jwt\Provider;

/** @codeCoverageIgnore */
final class JWTProvider
{
    public function publish(): void
    {
        $newFile = __DIR__.'./../../../../../config/jwt.php';
        if (!file_exists($newFile)) {
            copy(__DIR__ . './../../config/jwt-config.php', $newFile);
            echo 'JWT config has been copied for config/ dir.';
        } else {
            echo 'Existing JWT config file detected.';
        }
    }
}