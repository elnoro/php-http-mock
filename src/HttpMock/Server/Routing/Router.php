<?php

declare(strict_types=1);

namespace App\HttpMock\Server\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface Router
{
    public function resolve(Request $request): Response;
}
