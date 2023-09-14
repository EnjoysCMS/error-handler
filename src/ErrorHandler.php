<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use ErrorException;
use Throwable;

final class ErrorHandler
{

    public const ERROR_NAMES = [
        0 => 'Exception',
        E_ERROR => 'PHP Fatal Error',
        E_WARNING => 'PHP Warning',
        E_PARSE => 'PHP Parse Error',
        E_NOTICE => 'PHP Notice',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_WARNING => 'PHP User Warning',
        E_USER_NOTICE => 'PHP User Notice',
        E_STRICT => 'PHP Strict Warning',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_DEPRECATED => 'PHP Deprecated',
        E_USER_DEPRECATED => 'PHP User Deprecated',
    ];

    public function __construct(
        private readonly ExceptionHandlerInterface $exceptionHandler,
        private readonly ErrorLoggerInterface $logger
    ) {
    }


    /**
     * Catch Errors, Warning, etc
     * @throws ErrorException
     * @throws Throwable
     */
    public function catchErrors(): ErrorHandler
    {
        $this->displayErrors(false);
        $this->register();
        return $this;
    }

    public function displayErrors(bool $value): ErrorHandler
    {
        ini_set('display_errors', $value ? '1' : '0');
        return $this;
    }


    /**
     * Register this error handler.
     * @throws Throwable
     */
    public function register(): void
    {
        // Handles throwable, echo output and exit.
        set_exception_handler(function (Throwable $error): void {
            // disable error capturing to avoid recursive errors while handling exceptions
            $this->unregister();
            $this->exceptionHandler->handle($error);
        });

        // Handles PHP execution errors such as warnings and notices.
        set_error_handler(
            function (int $severity, string $message, string $file, int $line): bool {
                // Logging all php errors
                $this->logger->log(new PhpError($severity, $message, $file, $line));

                if (!(error_reporting() & $severity)) {
                    // This error code is not included in error_reporting.
                    return true;
                }

                if (self::isFatalError($severity)) {
                    throw new ErrorException(
                        sprintf('%s: %s', self::ERROR_NAMES[$severity] ?? '', $message),
                        0,
                        $severity,
                        $file,
                        $line
                    );
                }
                return true;
            }
        );

        // Handles fatal error.
        register_shutdown_function(function (): void {
            $e = error_get_last();

            if ($e !== null && self::isFatalError($e['type'])) {
                throw new ErrorException(
                    sprintf('%s: %s', self::ERROR_NAMES[$e['type']] ?? '', $e['message']),
                    0,
                    $e['type'],
                    $e['file'],
                    $e['line']
                );
            }
        });
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }


    public static function isFatalError(int $severity): bool
    {
        return in_array($severity, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_USER_ERROR,
//            E_USER_DEPRECATED,
//            E_DEPRECATED
        ], true);
    }


}
