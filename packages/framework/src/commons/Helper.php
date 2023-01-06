<?php

declare(strict_types=1);

namespace Aeatech\Commons;

final class Helper
{
    public static function trimPath(string $path): string
    {
        return '/' . rtrim(ltrim(trim($path), '/'), '/');
    }
}