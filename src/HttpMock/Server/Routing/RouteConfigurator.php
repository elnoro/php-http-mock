<?php

declare(strict_types=1);

namespace App\HttpMock\Server\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RouteConfigurator
{
    public function routeWillReturn(
        string $url = '/',
        string $method = Request::METHOD_GET,
        int $responseCode = Response::HTTP_OK,
        string $responseBody = ''
    ): void;
}
