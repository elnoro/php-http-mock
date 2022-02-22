<?php

declare(strict_types=1);

namespace App\HttpMock\Server\RequestLog;

use Symfony\Component\HttpFoundation\Request;

interface RequestRecorderInterface
{
    public function record(Request $request): void;
}
