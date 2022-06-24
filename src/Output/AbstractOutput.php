<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractOutput
{
    private \Throwable $error;
    private string $type;
    protected ResponseInterface $response;

    public function __construct(protected string $mimeType)
    {
        $this->response = new Response();
    }


    public function setError(\Throwable $error)
    {
        $this->error = $error;
        $this->type = get_class($error);
        return $this;
    }

    public function getError(): \Throwable
    {
        return $this->error;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
