<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Throwable;

interface ErrorHandlerInterface
{
    public function handle(Throwable $error): void;
}
