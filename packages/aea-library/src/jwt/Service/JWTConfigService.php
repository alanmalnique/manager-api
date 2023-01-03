<?php

declare(strict_types=1);

namespace Aeatech\Jwt\Service;

class JWTConfigService
{
    public function resolve(): array
    {
        $location = __DIR__.'./../../../../config/jwt.php';
        if (file_exists($location)) {
            $file = require($location);
        } else {
            $file = require(__DIR__.'./../../config/jwt-config.php');
        }
        return $file;
    }
}
