<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\ErrorHandler;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractOutput implements OutputInterface
{
    protected \Throwable $error;
    protected ResponseInterface $response;
    protected int $httpStatusCode = ErrorHandler::DEFAULT_STATUS_CODE;

    public function __construct(protected ?string $mimeType = null)
    {
        $this->response = new Response();
    }

    public function setHttpStatusCode(int $status): static
    {
        $this->httpStatusCode = $status;
        return $this;
    }

    public function setError(\Throwable $error): static
    {
        $this->error = $error;
        $this->type = get_class($error);
        return $this;
    }

}
