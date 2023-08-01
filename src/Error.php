<?php

namespace EnjoysCMS\ErrorHandler;

use Throwable;

final class Error
{
    public function __construct(
        private readonly Throwable $error,
        private readonly int $httpStatusCode,
        private readonly ?string $mimeType = null
    ) {
    }

    public function getError(): Throwable
    {
        return $this->error;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getType(): string
    {
        return $this->error::class;
    }

    public function getMessage(): string
    {
        return $this->error->getMessage();
    }

    public function getCode(): int
    {
        return $this->error->getCode();
    }
}
