<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Psr\Log\LoggerInterface;

interface ErrorLoggerInterface
{
    public function log(PhpError $error, array $logLevels = []): void;

    public function getPsrLogger(): LoggerInterface;
}
