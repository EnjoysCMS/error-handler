<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Throwable;

interface ExceptionHandlerInterface
{
    public const DEFAULT_STATUS_CODE = 500;

    public function handle(Throwable $error): void;
}
