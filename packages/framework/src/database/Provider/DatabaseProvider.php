<?php

declare(strict_types=1);

namespace Aeatech\Database\Provider;

use Aeatech\Database\Drivers\MySQL\MySQLDriver;
use Aeatech\Database\Interface\Database;
use Aeatech\Database\Service\DatabaseConfigService;

class DatabaseProvider
{
    private static Database $database;
    private static array $databaseProviders = [
        'mysql' => MySQLDriver::class
    ];

    public static function boot(): void
    {
        $config = (new DatabaseConfigService())->resolve();
        $driver = $config['driver'];
        self::$database = (new self::$databaseProviders[$driver]())->init();
    }

    public static function database(): Database
    {
        return self::$database;
    }

    public function publish(): void
    {
        $newFile = __DIR__.'./../../../../../config/database.php';
        if (!file_exists($newFile)) {
            copy(__DIR__ . './../../config/database.php', $newFile);
            echo 'Database config has been copied for config/ dir.';
        } else {
            echo 'Existing Database config file detected.';
        }
    }
}