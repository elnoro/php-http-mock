<?php

declare(strict_types=1);

namespace App\HttpMock\Server\RequestLog;

use App\HttpMock\Exception\ApiMockerException;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use Symfony\Component\HttpFoundation\Request;

final class FileRequestRecorder implements RequestRecorder, RequestRecordProvider
{
    public function __construct(private readonly string $logFile)
    {
    }

    public function record(Request $request): void
    {
        $logData = $this->parseLog();
        $url = $request->getRequestUri();
        $method = $request->getMethod();

        if (!isset($logData[$url])) {
            $logData[$url] = [];
        }
        if (!is_array($logData[$url])) {
            throw ApiMockerException::invalidRecords($url, $this->logFile);
        }
        $logData[$url][$method] = ['requestBody' => $request->getContent()];

        $isWritten = file_put_contents($this->logFile, json_encode($logData, JSON_PRETTY_PRINT));
        if (!$isWritten) {
            throw ApiMockerException::flushError($this->logFile);
        }
    }

    public function lastRequestOn(string $url, string $method): Request
    {
        $recordsData = $this->parseLog();
        if (!isset($recordsData[$url][$method]['requestBody'])) {
            throw ApiMockerException::recordNotFound($url, $this->logFile);
        }
        $requestBody = (string) $recordsData[$url][$method]['requestBody'];

        return Request::create($url, $method, content: $requestBody);
    }

    private function parseLog(): array
    {
        /** @psalm-suppress MixedAssignment */
        $logData = json_decode(file_get_contents($this->logFile), true);
        if (!is_array($logData)) {
            $logData = [];
        }

        return $logData;
    }
}
