<?php

declare(strict_types=1);

namespace App\HttpMock\Client;

use App\HttpMock\Exception\ApiMockerException;
use App\HttpMock\Server\HttpMockApp;
use App\HttpMock\Server\RequestLog\FileRequestRecorder;
use App\HttpMock\Server\RequestLog\RequestRecordProvider;
use App\HttpMock\Server\Routing\JsonFileRouter;
use App\HttpMock\Server\Routing\RouteConfigurator;
use const DIRECTORY_SEPARATOR;
use function implode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use function sys_get_temp_dir;
use function tempnam;

final class ApiMocker
{
    private ?Process $process = null;

    public static function create(int $port = 5934, ): self
    {
        $routesFile = tempnam(sys_get_temp_dir(), 'api_mocker_routes_');
        $routeConfigurator = new JsonFileRouter($routesFile);
        $recordsFile = tempnam(sys_get_temp_dir(), 'api_mocker_requests_');
        $recordProvider = new FileRequestRecorder($recordsFile);
        $indexFile = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', 'server.php']);

        $serverConfig = new ServerConfig($routesFile, $recordsFile, $indexFile, $port);

        return new self($routeConfigurator, $recordProvider, $serverConfig);
    }

    public function __construct(
        private readonly RouteConfigurator $routeConfigurator,
        private readonly RequestRecordProvider $recordProvider,
        private readonly ServerConfig $serverConfig,
    ) {
    }

    public function routeWillReturn(
        string $url = '/',
        string $method = Request::METHOD_GET,
        int $responseCode = Response::HTTP_OK,
        string $responseBody = ''
    ): void {
        $this->routeConfigurator->routeWillReturn($url, $method, $responseCode, $responseBody);
    }

    public function lastRequestOn(string $url, string $method): Request
    {
        return $this->recordProvider->lastRequestOn($url, $method);
    }

    public function start(): void
    {
        if (null !== $this->process) {
            return;
        }
        $this->process = new Process(
            ['php', '-S', 'localhost:'.$this->serverConfig->port, $this->serverConfig->indexFile]
        );
        $this->process->setEnv([
            HttpMockApp::ROUTES_FILE_ENV => $this->serverConfig->routesFile,
            HttpMockApp::RECORDS_FILE_ENV => $this->serverConfig->recordsFile,
        ]);
        $this->process->start();

        // server starts almost immediately, but waiting just in case
        // cannot use Process::waitCallback on all php versions, so checking the socket instead
        $socket = false;
        for ($i = 0; $i < $this->serverConfig->ticksToWait; ++$i) {
            $socket = @fsockopen('localhost', $this->serverConfig->port);
            if (false !== $socket) {
                break;
            }
            usleep(50000);
        }

        if (false === $socket) {
            throw ApiMockerException::serverStartTimeout($this->serverConfig->port);
        }
    }

    public function stop(): void
    {
        if (null === $this->process) {
            return;
        }

        $this->process->stop(0);
    }

    public function getBaseUri(): string
    {
        return 'http://localhost:'.$this->serverConfig->port.'/';
    }
}
