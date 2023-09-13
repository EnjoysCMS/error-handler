<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


interface ErrorLoggerInterface
{
    public function log(PhpError $error, array $logLevels = []): void;
}
