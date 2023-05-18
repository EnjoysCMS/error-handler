<?php

namespace EnjoysCMS\ErrorHandler;

class Error
{
    public function __construct(private \Throwable $error, private int $httpStatusCode, private ?string $mimeType = null)
    {
    }

    public function getError(): \Throwable
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
