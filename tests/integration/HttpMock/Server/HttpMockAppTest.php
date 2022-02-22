<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Server;

use App\HttpMock\Server\HttpMockApp;
use PHPUnit\Framework\TestCase;

use function putenv;
use function sys_get_temp_dir;
use function tempnam;

/**
 * @covers \App\HttpMock\Server\HttpMockApp
 */
final class HttpMockAppTest extends TestCase
{
    /**
     * @test
     */
    public function buildsItselfUsingEnvVars(): void
    {
        putenv('PHP_HTTP_MOCK_FILE='.tempnam(sys_get_temp_dir(), 'http_mock_app_unit_'));
        $result = HttpMockApp::fromEnv();

        $this->assertInstanceOf(HttpMockApp::class, $result);
    }

    /**
     * @test
     */
    public function checksEnvBeforeStarting(): void
    {
        putenv('PHP_HTTP_MOCK_FILE=');
        $this->expectExceptionMessage('Empty env PHP_HTTP_MOCK_FILE');
        HttpMockApp::fromEnv();
    }
}
