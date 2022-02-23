<?php

declare(strict_types=1);

namespace Test\Integration\App\HttpMock\Server\RequestLog;

use App\HttpMock\Server\RequestLog\FileRequestRecorder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @covers \App\HttpMock\Server\RequestLog\FileRequestRecorder
 */
final class FileRequestRecorderTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = tempnam(sys_get_temp_dir(), 'file_request_recorder_test');
    }

    protected function tearDown(): void
    {
        unlink($this->logFile);
    }

    /**
     * @test
     */
    public function recordsLastRequestToJsonFile(): void
    {
        $recorder = new FileRequestRecorder($this->logFile);

        $recorder->record(Request::create('expected-route', 'PUT', content: 'put body'));
        $recorder->record(Request::create('expected-route', 'POST', content: 'post body'));

        $recordedRequest = $recorder->lastRequestOn('expected-route', 'PUT');
        $this->assertSame('put body', $recordedRequest->getContent());

        $recordedRequest = $recorder->lastRequestOn('expected-route', 'POST');
        $this->assertSame('post body', $recordedRequest->getContent());
    }
}
