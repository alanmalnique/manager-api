<?php

declare(strict_types=1);

namespace Test\Aeatech\Jwt;

use Aeatech\Jwt\Exception\JWTExpiredException;
use Aeatech\Jwt\Exception\JWTInvalidFormatException;
use Aeatech\Jwt\Exception\JWTInvalidSignatureException;
use Aeatech\Jwt\JWT;
use Aeatech\Jwt\Service\JWTConfigService;
use Aeatech\Commons\Clock;
use PHPUnit\Framework\TestCase;

final class JWTTest extends TestCase
{
    private string $fakeToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2NzUyODk4ODAsInVzciI6ImZha2VWYWx1ZSJ9.dGFf2um8EgEBKyF45asa1KXLMrV4h--t_CnxqKeCCaU";
    public function setUp(): void
    {
        Clock::freeze('2023-02-01 18:18:00');
    }

    public function test_GenerateAction_ShouldReturnEncodedToken(): void
    {
        $configService = $this->mockConfigService();
        JWT::config($configService);
        JWT::generate('fakeValue');

        $this->assertEquals($this->fakeToken, JWT::token());
    }

    public function test_GenerateAction_ShouldReturnExpectedExpiration_WhenExpirationIs30Minutes(): void
    {
        $expectedExpiration = strtotime('2023-02-01 18:48:00');

        $configService = $this->mockConfigService('fakeKey', 30);
        JWT::config($configService);
        $generatedToken = JWT::generate('123');

        $this->assertEquals($expectedExpiration, $generatedToken['expires_at']);
    }

    public function test_GenerateAction_ShouldReturnExpectedExpiration_WhenExpirationIs60Minutes(): void
    {
        $expectedExpiration = strtotime('2023-02-01 19:18:00');

        $configService = $this->mockConfigService();
        JWT::config($configService);
        $generatedToken = JWT::generate('123');

        $this->assertEquals($expectedExpiration, $generatedToken['expires_at']);
    }

    public function test_ValidateAction_ShouldReturnStringFromGeneratedToken(): void
    {
        $expectedValue = "fakeValue";

        $configService = $this->mockConfigService();
        JWT::config($configService);
        $validatedValue = JWT::validate($this->fakeToken);

        $this->assertEquals($expectedValue, $validatedValue);
    }

    public function test_ValidateAction_ShouldThrowJWTInvalidFormatException_WhenProvidedTokenIsInvalid(): void
    {
        $this->expectException(JWTInvalidFormatException::class);
        $this->expectExceptionMessage('JWT invalid format.');

        $configService = $this->mockConfigService();
        JWT::config($configService);
        JWT::validate("wrong.value");
    }

    public function test_ValidateAction_ShouldThrowJWTExpiredException_WhenProvidedTokenIsExpired(): void
    {
        $this->expectException(JWTExpiredException::class);
        $this->expectExceptionMessage('JWT token expired.');

        Clock::freeze('2021-12-31 18:00:00');
        $configService = $this->mockConfigService();
        JWT::config($configService);
        JWT::generate('fakeValue');
        Clock::unfreeze();

        JWT::validate(JWT::token());

    }

    public function test_ValidateAction_ShouldThrowJWTInvalidSignatureException(): void
    {
        $this->expectException(JWTInvalidSignatureException::class);
        $this->expectExceptionMessage('JWT invalid signature.');

        $configService = $this->mockConfigService('mockKey');
        JWT::config($configService);

        JWT::validate($this->fakeToken);

    }

    private function mockConfigService(string $key = 'fakeKey', int $expiration = 60): JWTConfigService
    {
        $configService = $this->createMock(JWTConfigService::class);
        $configService->method('resolve')->willReturn(['key' => $key, 'expiration' => $expiration]);
        return $configService;
    }

    public function tearDown(): void
    {
        Clock::unfreeze();
    }
}