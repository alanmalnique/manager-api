<?php

declare(strict_types=1);

namespace Aeatech\Database\Service;

/** @codeCoverageIgnore */
class DatabaseConfigService
{
    public function resolve(): array
    {
        $location = __DIR__.'./../../../../../config/database.php';
        if (file_exists($location)) {
            $file = require($location);
        } else {
            $file = require(__DIR__.'./../../config/database.php');
        }
        return $file;
    }
}
