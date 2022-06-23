<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


final class Error
{

    private \Throwable $error;
    private ?string $type = null;

    public function __construct(\Throwable $error)
    {
        $this->error = $error;
        $this->type = get_class($error);
    }

    public function __toString(): string
    {
        $error = $this->getError();
        $message = $error->getMessage();

        return sprintf(
            '%s: %s',
            $this->getType(),
            (empty($message)) ? 'No message' : $message
        );
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getError(): \Throwable
    {
        return $this->error;
    }

}
