<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Server;

use App\HttpMock\Server\JsonFileResolver;
use PHPUnit\Framework\TestCase;

use Symfony\Component\HttpFoundation\Request;

use function file_put_contents;
use function unlink;

/**
 * @covers \App\HttpMock\Server\JsonFileResolver
 */
final class JsonFileResolverTest extends TestCase
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
    public function readsConfigurationFromAFile(string $config, int $expectedCode): void
    {
        file_put_contents(self::TEMP_CONFIG_PATH, $config);

        $resolver = JsonFileResolver::fromFile(self::TEMP_CONFIG_PATH);

        $request = Request::create('configured-uri', 'PATCH');
        $response = $resolver->resolve($request);

        $this->assertSame($expectedCode, $response->getStatusCode());

        unlink(self::TEMP_CONFIG_PATH);
    }

    public static function providerConfigs(): iterable
    {
        return [
            'valid config' => [self::VALID_CONFIG, 301],
            'empty config' => ['', 404],
        ];
    }
}
