<?php

declare(strict_types=1);

use App\HttpMock\Server\HttpMockApp;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/vendor/autoload.php';

$app = HttpMockApp::fromEnv();

$request = Request::createFromGlobals();
$response = $app->handle($request);

http_response_code($response->getStatusCode());

echo $response->getContent();