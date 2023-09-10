<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use RuntimeException;

final class DeprecationsHandler implements DeprecationsHandlerInterface
{
    private string $loggerLevel = 'notice';

    public function __construct(private LoggerInterface $logger, ?string $loggerLevel = null, ?string $name = null)
    {
        $this->withName($name);
        $this->setLoggerLevel($loggerLevel);
    }

    public function withName(?string $name): DeprecationsHandler
    {
        if ($this->logger instanceof Logger && $name) {
            $this->logger = $this->logger->withName($name);
        }
        return $this;
    }

    public function register(): void
    {
        set_error_handler(
            function (int $severity, string $message, string $file, int $line): bool {
                if (in_array($severity, [E_USER_DEPRECATED, E_DEPRECATED], true)) {
                    $this->logger->log(
                        $this->loggerLevel,
                        sprintf(
                            'Deprecation: %s in %s on line %s',
                            $message,
                            $file,
                            $line
                        )
                    );
                }
                return true;
            }
        );
    }

    public function setLoggerLevel(?string $level): DeprecationsHandler
    {
        if ($level === null) {
            return $this;
        }

        $logLevels = array_keys((new ReflectionClass(LogLevel::class))->getConstants());
        if (!in_array(strtoupper($level), $logLevels, true)) {
            throw new RuntimeException(
                sprintf(
                    '%s not allowed, allowed only (%s)',
                    $level,
                    implode(
                        ', ',
                        $logLevels
                    )
                )
            );
        }
        $this->loggerLevel = $level;
        return $this;
    }
}
