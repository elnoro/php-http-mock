<?php

declare(strict_types=1);

namespace App\HttpMock\Server;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponseResolverInterface
{
    public function resolve(Request $request): Response;
}
