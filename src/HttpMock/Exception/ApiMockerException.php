<?php

declare(strict_types=1);

namespace App\HttpMock\Exception;

use function sprintf;
use UnexpectedValueException;

/**
 * @codeCoverageIgnore
 */
final class ApiMockerException extends UnexpectedValueException
{
    public static function invalidRoutes(string $routesFile): self
    {
        throw new self(sprintf('Cannot load routes from %s', $routesFile));
    }

    public static function invalidRecords(string $url, string $recordsFile): self
    {
        throw new self(sprintf('Cannot load records for %s from %s', $url, $recordsFile));
    }

    public static function recordNotFound(string $url, string $recordsFile): self
    {
        throw new self(sprintf('No record found in %s for %s', $url, $recordsFile));
    }

    public static function flushError(string $file): self
    {
        throw new self(sprintf('Failed to save data to %s', $file));
    }

    public static function serverStartTimeout(int $port): self
    {
        throw new self(sprintf('Could not start mock on port %d', $port));
    }
}
