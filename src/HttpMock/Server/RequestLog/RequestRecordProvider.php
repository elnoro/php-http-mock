<?php

declare(strict_types=1);

namespace App\HttpMock\Server\RequestLog;

use Symfony\Component\HttpFoundation\Request;

interface RequestRecordProvider
{
    public function lastRequestOn(string $url, string $method): Request;
}
