<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\View;


use EnjoysCMS\ErrorHandler\ExceptionHandler;
use EnjoysCMS\ErrorHandler\ExceptionHandlerInterface;
use Throwable;

interface ViewInterface
{
    public function getContent(Throwable $error, int $statusCode = ExceptionHandlerInterface::DEFAULT_STATUS_CODE): string;
}
