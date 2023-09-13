<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Throwable;

final class PhpError
{
    public function __construct(
        public readonly int $severity,
        public readonly string $message,
        public readonly string $file,
        public readonly int $line,
        public readonly array $trace = []
    ) {
    }

    public static function fromThrowable(Throwable $error): PhpError
    {
        return new PhpError(
            severity: 0,
            message: sprintf('%s: %s', $error::class, $error->getMessage()),
            file: $error->getFile(),
            line: $error->getLine(),
            trace: $error->getTrace()
        );
    }
}
