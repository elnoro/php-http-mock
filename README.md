# php-http-mock

![ci](https://github.com/elnoro/php-http-mock/actions/workflows/php.yml/badge.svg)
[![coverage](https://codecov.io/gh/elnoro/php-http-mock/branch/main/graph/badge.svg)](https://app.codecov.io/gh/elnoro/php-http-mock)


A simple solution to mock external APIs using local php server without binary deps

Designed for PHPUnit, but you can use it with anything.

## How it works

Whenever you call ApiMocker::start, a new local php server is launched (via php -S).

The server reads its route configuration (URLs, methods, and corresponding responses) from a temporary file. This file
is re-read on every request, so you can reconfigure routes and responses after the server is started.

The file is written to and created by the php-http-mock package automatically and passed to the server via an env
variable.

## Example

```php
<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Client;

use App\HttpMock\Client\ApiMocker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function returnsRequestsMadeToApiServer(): void
    {
        $this->apiMocker->routeWillReturn('/request?to=keep', 'POST');
        $this->httpClient->request('POST', '/request?to=keep', ['body' => 'expected-body']);

        $request = $this->apiMocker->lastRequestOn('/request?to=keep', 'POST');

        $this->assertSame('expected-body', $request->getContent());
    }
}
```

## Commands for development

`composer test` - self-evident

`composer cov` - generates coverage html & xml

`coverage ci` - runs php-cs-fixer, psalm and tests

`coverage fixcs` - fixes code style automatically