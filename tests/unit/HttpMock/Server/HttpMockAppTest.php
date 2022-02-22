<?php

declare(strict_types=1);

namespace Test\Unit\App\HttpMock\Server;

use App\HttpMock\Server\HttpMockApp;
use App\HttpMock\Server\ResponseResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\HttpMock\Server\HttpMockApp
 */
final class HttpMockAppTest extends TestCase
{
    private ResponseResolverInterface $responseResolver;
    private HttpMockApp $httpMockApp;

    protected function setUp(): void
    {
        $this->responseResolver = $this->createMock(ResponseResolverInterface::class);
        $this->httpMockApp = new HttpMockApp($this->responseResolver);
    }

    /**
     * @test
     */
    public function delegatesRequestsToResolver(): void
    {
        $expectedRequest = $this->createMock(Request::class);
        $expectedResponse = $this->createMock(Response::class);

        $this->responseResolver
            ->method('resolve')
            ->with($expectedRequest)
            ->willReturn($expectedResponse);

        $actualResponse = $this->httpMockApp->handle($expectedRequest);

        $this->assertSame($expectedResponse, $actualResponse);
    }
}
