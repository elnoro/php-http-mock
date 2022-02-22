<?php

declare(strict_types=1);

namespace Test\Unit\App\HttpMock\Server;

use App\HttpMock\Server\JsonFileResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \App\HttpMock\Server\JsonFileResolver
 */
final class JsonFileResolverTest extends TestCase
{
    private JsonFileResolver $jsonFileResolver;

    protected function setUp(): void
    {
        $this->jsonFileResolver = new JsonFileResolver([
            'configured-uri' => ['PATCH' => ['code' => 301, 'body' => 'expected body']]
        ]);
    }

    /**
     * @test
     */
    public function returns404OnUnkownUri(): void
    {
        $request = Request::create('unknown-uri', 'PATCH');

        $response = $this->jsonFileResolver->resolve($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function returnsConfiguredResponseOnKnownUriAndMethod(): void
    {
        $request = Request::create('configured-uri', 'PATCH');

        $response = $this->jsonFileResolver->resolve($request);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('expected body', $response->getContent());
    }
}
