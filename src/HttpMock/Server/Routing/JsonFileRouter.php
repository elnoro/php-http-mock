<?php

declare(strict_types=1);

namespace App\HttpMock\Server\Routing;

use App\HttpMock\Exception\ApiMockerException;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class JsonFileRouter implements Router, RouteConfigurator
{
    public static function fromFile(string $file): self
    {
        return new self($file);
    }

    public function __construct(public readonly string $file)
    {
    }

    public function resolve(Request $request): Response
    {
        $url = $request->getRequestUri();
        $method = $request->getMethod();

        $routeConfig = $this->reloadConfig()[$url][$method] ?? null;
        if (!isset($routeConfig['body'], $routeConfig['code'])) {
            return $this->defaultResponse($url, $method);
        }

        return new Response((string) $routeConfig['body'], (int) $routeConfig['code']);
    }

    public function routeWillReturn(
        string $url = '/',
        string $method = Request::METHOD_GET,
        int $responseCode = Response::HTTP_OK,
        string $responseBody = ''
    ): void {
        $config = $this->reloadConfig();
        if (!isset($config[$url])) {
            $config[$url] = [];
        }
        if (!is_array($config[$url])) {
            throw ApiMockerException::invalidRoutes($this->file);
        }

        $config[$url][$method] = ['code' => $responseCode, 'body' => $responseBody];

        $this->dumpConfig($config);
    }

    private function reloadConfig(): array
    {
        /** @psalm-suppress MixedAssignment */
        $responses = json_decode(file_get_contents($this->file), true);
        if (!is_array($responses)) {
            $responses = [];
        }

        return $responses;
    }

    private function dumpConfig(array $config): void
    {
        $isWritten = file_put_contents($this->file, json_encode($config, JSON_PRETTY_PRINT));
        if (!$isWritten) {
            throw ApiMockerException::flushError($this->file);
        }
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
