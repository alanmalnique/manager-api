<?php

declare(strict_types=1);

namespace App\Provider;

use Aeatech\Database\Provider\DatabaseProvider as Provider;

final class DatabaseProvider extends Provider
{
    public static function boot(): void
    {
        parent::boot();
    }
}
