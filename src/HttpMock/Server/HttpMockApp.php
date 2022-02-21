<?php

declare(strict_types=1);

namespace App\HttpMock\Server;

use function getenv;
use function sprintf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpMockApp
{
    public const ROUTES_FILE_ENV = 'PHP_HTTP_MOCK_FILE';

    public static function fromEnv(): self
    {
        $routesFile = getenv(self::ROUTES_FILE_ENV);
        if (!$routesFile) {
            throw new \UnexpectedValueException(sprintf('Empty env %s', self::ROUTES_FILE_ENV));
        }

        return new self(JsonFileResolver::fromFile($routesFile));
    }

    public function __construct(
        private readonly ResponseResolverInterface $responseResolver
    ) {
    }

    public function handle(Request $request): Response
    {
        // TODO add response history for api mocker to query
        return $this->responseResolver->resolve($request);
    }
}
