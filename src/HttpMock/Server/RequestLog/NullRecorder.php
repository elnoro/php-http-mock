<?php

declare(strict_types=1);

namespace App\HttpMock\Server\RequestLog;

use Symfony\Component\HttpFoundation\Request;

/**
 * @codeCoverageIgnore
 */
final class NullRecorder implements RequestRecorder
{
    public function record(Request $request): void
    {
    }
}
