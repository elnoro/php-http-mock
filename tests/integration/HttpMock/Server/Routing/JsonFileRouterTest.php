<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Server\Routing;

use App\HttpMock\Server\Routing\JsonFileRouter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @covers \App\HttpMock\Server\Routing\JsonFileRouter
 */
final class JsonFileRouterTest extends TestCase
{
    private const TEMP_CONFIG_PATH = 'testconfig_removeme.json';
    private const VALID_CONFIG = <<<'CONFIG'
        {
            "configured-uri": {
                "PATCH": {
                   "code": 301,
                   "body": "expected body"
                }
            }
        }
    CONFIG;

    /**
     * @dataProvider providerConfigs
     * @test
     */
    public function readsConfigurationFromAFile(string $config, int $expectedCode, string $expectedResponseBody): void
    {
        file_put_contents(self::TEMP_CONFIG_PATH, $config);

        $router = JsonFileRouter::fromFile(self::TEMP_CONFIG_PATH);

        $request = Request::create('configured-uri', 'PATCH');
        $response = $router->resolve($request);

        $this->assertSame($expectedCode, $response->getStatusCode());
        $this->assertSame($expectedResponseBody, $response->getContent());

        unlink(self::TEMP_CONFIG_PATH);
    }

    /**
     * @test
     */
    public function savesConfigurationToJsonFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'api_mock_unit_test');

        $router = JsonFileRouter::fromFile($tmpFile);

        $router->routeWillReturn('/get-route', 'GET', 111, 'get response body');
        $router->routeWillReturn('/post-route', 'POST', 222, 'post response body');

        $router->routeWillReturn('/multi-route', 'PATCH', 333, 'multi response body 1');
        $router->routeWillReturn('/multi-route', 'TRACE', 444, 'multi response body 2');

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

    public static function providerConfigs(): iterable
    {
        return [
            'valid config' => [self::VALID_CONFIG, 301, 'expected body'],
            'empty config' => ['', 404, '{"url":"configured-uri","method":"PATCH","error":"no route configured!"}'],
        ];
    }
}
