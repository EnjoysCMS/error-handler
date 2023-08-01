<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\View;


use EnjoysCMS\ErrorHandler\ErrorHandler;
use Throwable;

interface ViewInterface
{
    public function getContent(Throwable $error, int $statusCode = ErrorHandler::DEFAULT_STATUS_CODE): string;
}
