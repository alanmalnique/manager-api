<?php

declare(strict_types=1);

namespace Aeatech\Router\Response;

use Aeatech\Router\Response\Parser\ResponseParser;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

final class Response
{
    private static BaseResponse $response;

    /**
     * @return BaseResponse
     */
    public static function getResponse(): BaseResponse
    {
        return self::$response;
    }

    private function __construct(BaseResponse $response)
    {
        self::$response = $response;
    }

    public static function json(array $body, int $statusCode = 200): Response
    {
        return new self(ResponseParser::parse($body, $statusCode));
    }

    public function setHeader(string $name, string $value): void
    {
        self::$response->headers->set($name, $value);
    }
}
