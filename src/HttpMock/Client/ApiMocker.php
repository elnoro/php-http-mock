<?php

declare(strict_types=1);

namespace App\HttpMock\Client;

use App\HttpMock\Exception\ApiMockerException;
use App\HttpMock\Server\HttpMockApp;
use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use function sys_get_temp_dir;
use function tempnam;

/**
 * TODO move all file manipulation and json parsing into respective classes (Recorder and Resolver) to the internals in one place.
 */
final class ApiMocker
{
    private ?Process $process = null;

    public static function create(int $port = 5934): self
    {
        $routesFile = tempnam(sys_get_temp_dir(), 'api_mocker_routes_');
        $recordsFile = tempnam(sys_get_temp_dir(), 'api_mocker_requests_');
        $indexFile = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', 'server.php']);

        return new self($routesFile, $port, $recordsFile, $indexFile);
    }

    public function __construct(
        private readonly string $routesFile,
        private readonly int $port,
        private readonly string $recordsFile,
        private readonly string $indexFile,
        private readonly int $ticksToWait = 60,
    ) {
    }

    public function routeWillReturn(
        string $url = '/',
        string $method = Request::METHOD_GET,
        int $responseCode = Response::HTTP_OK,
        string $responseBody = ''
    ): void {
        // not thread safe! also loads the whole file in memory, but should be fine in this case
        $contents = file_get_contents($this->routesFile);
        $config = $contents ? json_decode($contents, true) : [];
        if (!is_array($config)) {
            throw ApiMockerException::invalidRoutes($this->routesFile);
        }
        if (!isset($config[$url])) {
            $config[$url] = [];
        }
        if (!is_array($config[$url])) {
            throw ApiMockerException::invalidRoutes($this->routesFile);
        }

        $config[$url][$method] = ['code' => $responseCode, 'body' => $responseBody];

        $isWritten = file_put_contents($this->routesFile, json_encode($config, JSON_PRETTY_PRINT));
        if (!$isWritten) {
            throw ApiMockerException::saveConfigError($this->routesFile);
        }
    }

    public function lastRequestOn(string $url, string $method): Request
    {
        $contents = file_get_contents($this->recordsFile);
        $recordsData = $contents ? json_decode($contents, true) : [];
        if (!is_array($recordsData)) {
            throw ApiMockerException::invalidRecords($this->recordsFile);
        }
        if (!isset($recordsData[$url][$method]['requestBody'])) {
            throw ApiMockerException::recordNotFound($url, $this->recordsFile);
        }
        $requestBody = (string) $recordsData[$url][$method]['requestBody'];

        return Request::create($url, $method, content: $requestBody);
    }

    public function start(): void
    {
        if (null !== $this->process) {
            return;
        }
        $this->process = new Process(['php', '-S', 'localhost:'.$this->port, $this->indexFile]);
        $this->process->setEnv([
            HttpMockApp::ROUTES_FILE_ENV => $this->routesFile,
            HttpMockApp::RECORDS_FILE_ENV => $this->recordsFile,
        ]);
        $this->process->start();

        // server starts almost immediately, but waiting just in case
        // cannot use Process::waitCallback on all php versions, so checking the socket instead
        $socket = false;
        for ($i = 0; $i < $this->ticksToWait; ++$i) {
            $socket = @fsockopen('localhost', $this->port);
            if (false !== $socket) {
                break;
            }
            usleep(50000);
        }

        if (false === $socket) {
            throw ApiMockerException::serverStartTimeout($this->port);
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
        return 'http://localhost:'.$this->port.'/';
    }
}
