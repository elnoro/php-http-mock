<?php

declare(strict_types=1);

namespace App\HttpMock\Server;

use App\HttpMock\Server\RequestLog\FileRequestRecorder;
use App\HttpMock\Server\RequestLog\NullRecorder;
use App\HttpMock\Server\RequestLog\RequestRecorder;
use App\HttpMock\Server\Routing\JsonFileRouter;
use App\HttpMock\Server\Routing\Router;
use function getenv;
use function sprintf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpMockApp
{
    public const ROUTES_FILE_ENV = 'PHP_HTTP_MOCK_FILE';
    public const RECORDS_FILE_ENV = 'PHP_HTTP_RECORDS_FILE';

    public static function fromEnv(): self
    {
        $routesFile = getenv(self::ROUTES_FILE_ENV);
        if (!$routesFile) {
            throw new \UnexpectedValueException(sprintf('Empty env %s', self::ROUTES_FILE_ENV));
        }
        $router = JsonFileRouter::fromFile($routesFile);

        $recordsFile = getenv(self::RECORDS_FILE_ENV);
        $recorder = $recordsFile ? new FileRequestRecorder($recordsFile) : new NullRecorder();

        return new self($router, $recorder);
    }

    public function __construct(
        private readonly Router $router,
        private readonly RequestRecorder $requestLog = new NullRecorder(),
    ) {
    }

    public function handle(Request $request): Response
    {
        $this->requestLog->record($request);

        return $this->router->resolve($request);
    }
}
