<?php

declare(strict_types=1);

namespace App\HttpMock\Server;

use function file_get_contents;
use function is_array;
use function json_decode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class JsonFileResolver implements ResponseResolverInterface
{
    private readonly array $responses;

    public static function fromFile(string $file): self
    {
        /** @psalm-suppress MixedAssignment */
        $responses = json_decode(file_get_contents($file), true);
        if (!is_array($responses)) {
            $responses = [];
        }

        return new self($responses);
    }

    private function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function resolve(Request $request): Response
    {
        $url = $request->getRequestUri();
        $method = $request->getMethod();

        $routeConfig = $this->responses[$url][$method] ?? null;
        if (!isset($routeConfig['body'], $routeConfig['code'])) {
            return $this->defaultResponse($url, $method);
        }

        return new Response((string) $routeConfig['body'], (int) $routeConfig['code']);
    }

    private function defaultResponse(string $url, string $method): Response
    {
        return new JsonResponse([
            'url' => $url,
            'method' => $method,
            'error' => 'no route configured!',
        ], Response::HTTP_NOT_FOUND);
    }
}
