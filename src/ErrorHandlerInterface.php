<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerInterface
{
    public function handle(\Throwable $error): void;
}
