<?php

declare(strict_types=1);

namespace App\HttpMock\Client;

/**
 * @codeCoverageIgnore
 */
final class ServerConfig
{
    public function __construct(
        public readonly string $routesFile,
        public readonly string $recordsFile,
        public readonly string $indexFile,
        public readonly int $port,
        public readonly int $ticksToWait = 60,
    ) {
    }
}
