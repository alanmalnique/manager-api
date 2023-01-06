<?php

declare(strict_types=1);

namespace Aeatech\Router\Response\Log;

final class ResponseLogger
{
    public static function log(array $trace): void
    {
        $content = self::parse($trace);
        $logFolder = __DIR__.'./../../../../../../storage/logs/';
        $logFile = $logFolder.date("Ymd").'.log';
        if (!file_exists($logFile)) {
            // create directory/folder uploads.
            @mkdir($logFolder, 0777, true);
        }
        file_put_contents($logFile, $content . "\n", FILE_APPEND);
    }

    private static function parse(array $trace): string
    {
        return '['.date("Y-m-d H:i:s").'] '.json_encode($trace);
    }
}
