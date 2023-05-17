<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


interface ErrorHandlerInterface
{
    public function handle(\Throwable $error): void;
}
