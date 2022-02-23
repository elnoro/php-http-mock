<?php

declare(strict_types=1);

namespace Test\Unit\App\HttpMock\Client;

use App\HttpMock\Client\ApiMocker;
use App\HttpMock\Client\ServerConfig;
use App\HttpMock\Server\RequestLog\RequestRecordProvider;
use App\HttpMock\Server\Routing\RouteConfigurator;
use PHPUnit\Framework\TestCase;


/**
 * @covers \App\HttpMock\Client\ApiMocker
 */
final class ApiMockerTest extends TestCase
{
    /**
     * @test
     */
    public function returnsBaseUriBasedOnPort(): void
    {
        $apiMocker = $this->createApiMocker(port: 9999);

        $this->assertSame('http://localhost:9999/', $apiMocker->getBaseUri());
    }

    /**
     * @test
     */
    public function checksIfServerStarts(): void
    {
        $this->expectExceptionMessage('Could not start mock on port -1');
        $apiMocker = $this->createApiMocker(routesFile: 'does-not-exist', port: -1, indexFile: 'does-not-exist');

        $apiMocker->start();
    }

    /**
     * @test
     */
    public function doesNothingIfServerIsNotStarted(): void
    {
        $apiMocker = $this->createApiMocker(routesFile: 'does-not-exist', port: -1, indexFile: 'does-not-exist');

        $apiMocker->stop();

        $this->assertTrue(true); // avoid warnings
    }

    private function createApiMocker(string $routesFile = '', int $port = 0, string $indexFile = ''): ApiMocker
    {
        $serverConfig = new ServerConfig($routesFile, '', $indexFile, $port, 0);
        $routeConfigurator = $this->createMock(RouteConfigurator::class);
        $recordProvider = $this->createMock(RequestRecordProvider::class);

        return new ApiMocker($routeConfigurator, $recordProvider, $serverConfig);
    }
}
