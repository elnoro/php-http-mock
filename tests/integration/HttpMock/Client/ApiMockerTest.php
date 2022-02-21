<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Client;

use App\HttpMock\Client\ApiMocker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @covers \App\HttpMock\Client\ApiMocker
 */
final class ApiMockerTest extends TestCase
{
    private ApiMocker $apiMocker;
    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->apiMocker = ApiMocker::create();
        $this->apiMocker->start();

        $this->httpClient = HttpClient::createForBaseUri($this->apiMocker->getBaseUri());
    }

    protected function tearDown(): void
    {
        $this->apiMocker->stop();
    }

    /**
     * @test
     */
    public function setsUpEmptyResponseByDefault(): void
    {
        $this->apiMocker->routeWillReturn('/expected-uri');

        $response = $this->httpClient->request('GET', '/expected-uri');

        $this->assertSame('', $response->getContent(false));
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function allowsDifferentResponsesOnTheSameUri(): void
    {
        $this->apiMocker->routeWillReturn('/expected-uri', responseCode: 400, responseBody: 'get response');
        $this->apiMocker->routeWillReturn('/expected-uri', 'POST', 500, 'post response');

        $getResponse = $this->httpClient->request('GET', '/expected-uri');
        $this->assertSame(400, $getResponse->getStatusCode());
        $this->assertSame('get response', $getResponse->getContent(false));

        $postResponse = $this->httpClient->request('POST', '/expected-uri');
        $this->assertSame(500, $postResponse->getStatusCode());
        $this->assertSame('post response', $postResponse->getContent(false));
    }

    /**
     * @test
     */
    public function returns404WhenNoUriIsConfigured(): void
    {
        $response = $this->httpClient->request('GET', '/invalid-uri');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(
            '{"url":"\/invalid-uri","method":"GET","error":"no route configured!"}',
            $response->getContent(false)
        );
    }
}
