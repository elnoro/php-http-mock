<?php

declare(strict_types=1);

namespace Test\Unit\App\HttpMock\Server;

use App\HttpMock\Server\HttpMockApp;
use App\HttpMock\Server\RequestLog\RequestRecorder;
use App\HttpMock\Server\Routing\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\HttpMock\Server\HttpMockApp
 */
final class HttpMockAppTest extends TestCase
{
    private Router $router;
    private RequestRecorder $requestRecorder;
    private HttpMockApp $httpMockApp;

    protected function setUp(): void
    {
        $this->router = $this->createMock(Router::class);
        $this->requestRecorder = $this->createMock(RequestRecorder::class);

        $this->httpMockApp = new HttpMockApp($this->router, $this->requestRecorder);
    }

    /**
     * @test
     */
    public function delegatesRequestsToResolver(): void
    {
        $expectedRequest = $this->createMock(Request::class);
        $expectedResponse = $this->createMock(Response::class);

        $this->router
            ->method('resolve')
            ->with($expectedRequest)
            ->willReturn($expectedResponse);

        $actualResponse = $this->httpMockApp->handle($expectedRequest);

        $this->assertSame($expectedResponse, $actualResponse);
    }

    /**
     * @test
     */
    public function recordsRequest(): void
    {
        $expectedRequest = $this->createMock(Request::class);

        $this->requestRecorder
            ->expects($this->once())
            ->method('record')
            ->with($expectedRequest);

        $actualResponse = $this->httpMockApp->handle($expectedRequest);
    }
}
