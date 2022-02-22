<?php

declare(strict_types=1);

namespace Test\Unit\App\HttpMock\Client;

use App\HttpMock\Client\ApiMocker;
use PHPUnit\Framework\TestCase;

use Throwable;

use function fclose;
use function file_get_contents;
use function fopen;
use function json_decode;
use function sys_get_temp_dir;
use function tempnam;


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
    public function savesConfigurationToJsonFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'api_mock_unit_test');

        $apiMocker = $this->createApiMocker(routesFile: $tmpFile);

        $apiMocker->routeWillReturn('/get-route', 'GET', 111, 'get response body');
        $apiMocker->routeWillReturn('/post-route', 'POST', 222, 'post response body');

        $apiMocker->routeWillReturn('/multi-route', 'PATCH', 333, 'multi response body 1');
        $apiMocker->routeWillReturn('/multi-route', 'TRACE', 444, 'multi response body 2');

        $actualConfig = json_decode(file_get_contents($tmpFile), true);

        $this->assertSame([
            '/get-route' => ['GET' => ['code' => 111, 'body' => 'get response body']],
            '/post-route' => ['POST' => ['code' => 222, 'body' => 'post response body']],
            '/multi-route' => [
                'PATCH' => ['code' => 333, 'body' => 'multi response body 1'],
                'TRACE' => ['code' => 444, 'body' => 'multi response body 2'],
            ],
        ], $actualConfig);
    }

    /**
     * @test
     */
    public function checksIfServerStarts(): void
    {
        $this->expectExceptionMessage('Could not start mock on port -1');
        $apiMocker = $this->createApiMocker(indexFile: 'does-not-exist', port: -1);

        $apiMocker->start();
    }

    /**
     * @test
     */
    public function doesNothingIfServerIsNotStarted(): void
    {
        $apiMocker = $this->createApiMocker(indexFile: 'does-not-exist', port: -1);

        $apiMocker->stop();

        $this->assertTrue(true); // avoid warnings
    }

    private function createApiMocker(string $routesFile = '', int $port = 0, string $recordsFile = '', string $indexFile = ''): ApiMocker
    {
        return new ApiMocker($routesFile, $port, $indexFile, $recordsFile, ticksToWait: 0);
    }
}
