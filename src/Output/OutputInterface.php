<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use Psr\Http\Message\ResponseInterface;

interface OutputInterface
{
    public function setError(\Throwable $error);
    public function setHttpStatusCode(int $status);
    public function getResponse(): ResponseInterface;
}
