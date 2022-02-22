<?php

declare(strict_types=1);

namespace App\HttpMock\Server\RequestLog;

use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use function sprintf;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

final class FileRequestRecorder implements RequestRecorderInterface
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
            throw new UnexpectedValueException('Cannot parse request record, malformed data');
        }
        $logData[$url][$method] = ['requestBody' => $request->getContent()];

        $isWritten = file_put_contents($this->logFile, json_encode($logData, JSON_PRETTY_PRINT));
        if (!$isWritten) {
            throw new UnexpectedValueException(sprintf('Cannot record request %s %s to %s', $request->getMethod(), $request->getUri(), $this->logFile));
        }
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
