<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use Psr\Http\Message\ResponseInterface;

interface OutputInterface
{
    public function setError(\Throwable $error): OutputInterface;
    public function setHttpStatusCode(int $status): OutputInterface;
    public function getResponse(): ResponseInterface;
}
