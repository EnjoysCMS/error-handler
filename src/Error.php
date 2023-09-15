<?php

namespace EnjoysCMS\ErrorHandler;

use Throwable;

final class Error
{
    private function __construct(
        public readonly string $message,
        public readonly string $file,
        public readonly int $line,
        public readonly string $type,
        public readonly int $code,
        public readonly int $httpStatusCode,
        public readonly string $traceString = '',
        public readonly array $trace = [],
        public readonly ?string $mimeType = null
    ) {
    }

    public static function createFromThrowable(Throwable $error, int $httpStatusCode, ?string $mimeType = null): Error
    {
        return new self(
            message: $error->getMessage(),
            file: $error->getFile(),
            line: $error->getLine(),
            type: $error::class,
            code: $error->getCode(),
            httpStatusCode: $httpStatusCode,
            traceString: $error->getTraceAsString(),
            trace: $error->getTrace(),
            mimeType: $mimeType
        );
    }
}
