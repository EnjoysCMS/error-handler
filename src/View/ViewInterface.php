<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\View;


use EnjoysCMS\ErrorHandler\ErrorHandler;

interface ViewInterface
{
    public function getBody(\Throwable $error, int $statusCode = ErrorHandler::DEFAULT_STATUS_CODE): string;
}
