<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use RuntimeException;

final class DeprecationsHandler implements DeprecationsHandlerInterface
{
    private string $loggerType;

    public function __construct(private LoggerInterface $logger, ?string $name = null, string $loggerType = 'notice')
    {
        if ($this->logger instanceof Logger && $name) {
            $this->logger = $this->logger->withName($name);
        }
        $this->loggerType = $loggerType;
    }

    public function register(): void
    {
        set_error_handler(
            function (int $severity, string $message, string $file, int $line): bool {
                if (in_array($severity, [E_USER_DEPRECATED, E_DEPRECATED], true)) {
                    $this->logger->{$this->loggerType}(
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

    public function setLoggerType(string $method): DeprecationsHandler
    {
        if (!method_exists($this->logger, $method) || $method === 'log') {
            throw new RuntimeException(
                sprintf(
                    '%s not allowed, allowed only (%s)',
                    $method,
                    implode(
                        ', ',
                        array_filter(
                            array_map(fn($item) => $item->name,
                                (new ReflectionClass(LoggerInterface::class))->getMethods()
                            ),
                            function ($item) {
                                return $item !== 'log';
                            }
                        )
                    )
                )
            );
        }
        $this->loggerType = $method;
        return $this;
    }
}
