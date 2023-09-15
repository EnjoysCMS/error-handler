<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\View;


use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\ExceptionHandler;
use EnjoysCMS\ErrorHandler\ExceptionHandlerInterface;
use Throwable;

interface ViewInterface
{
    public function getContent(Error $error, int $statusCode = ExceptionHandlerInterface::DEFAULT_STATUS_CODE): string;
}
