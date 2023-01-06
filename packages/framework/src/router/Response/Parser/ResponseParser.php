<?php

declare(strict_types=1);

namespace Aeatech\Router\Response\Parser;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ResponseParser
{
    public static function parse(array $body, int $statusCode): JsonResponse
    {
        return JsonResponse::fromJsonString(json_encode($body), $statusCode);
    }
}
