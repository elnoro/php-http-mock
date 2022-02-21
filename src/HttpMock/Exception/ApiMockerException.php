<?php

declare(strict_types=1);

namespace App\HttpMock\Exception;

use function sprintf;
use UnexpectedValueException;

final class ApiMockerException extends UnexpectedValueException
{
    public static function invalidConfig(string $routesFile): self
    {
        throw new self(sprintf('Cannot load routes from config to %s', $routesFile));
    }

    public static function saveConfigError(string $routesFile): self
    {
        throw new self(sprintf('Failed to save config to %s', $routesFile));
    }

    public static function serverStartTimeout(int $port): self
    {
        throw new UnexpectedValueException(sprintf('Could not start mock on port %d', $port));
    }
}
