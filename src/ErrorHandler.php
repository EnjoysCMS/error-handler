<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use EnjoysCMS\Core\Interfaces\EmitterInterface;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Image;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\OutputInterface;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Svg;
use EnjoysCMS\ErrorHandler\Output\Xml;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ErrorHandler implements ErrorHandlerInterface
{
    private const  PROCESSORS_MAP = [
        Json::class => ['application/json', 'text/json'],
        Html::class => ['text/html'],
        Xml::class => ['text/xml'],
        Plain::class => ['text/plain', 'text/css', 'text/javascript'],
        Svg::class => ['image/svg+xml'],
        Image::class => ['image/gif', 'image/jpeg', 'image/png', 'image/webp']
    ];

    private const ERROR_NAMES = [
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
        E_DEPRECATED => 'PHP Deprecated Warning',
        E_USER_DEPRECATED => 'PHP User Deprecated Warning',
    ];

    public const DEFAULT_STATUS_CODE = 500;

    /**
     * @var array<string, array{loggerType: list<string>, statusCode: int}>
     */
    private array $errorsMap = [];

    /**
     * @var array<int, list<string>>
     */
    private array $mappingLoggerType = [];
    private LoggerInterface $logger;
    private bool $allowQuit = false;


    public function __construct(
        private ServerRequestInterface $request,
        private EmitterInterface $emitter,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @throws \Throwable
     */
    public function handle(\Throwable $error): void
    {
        // disable error capturing to avoid recursive errors while handling exceptions
        $this->unregister();

        try {
            $httpStatusCode = $this->getStatusCode($error);
            $this->sendToLogger($error, $this->mappingLoggerType[$httpStatusCode] ?? []);

            $output = $this->getOutputProcessor();

            $this->emitter->emit(
                $output
                    ->setError($error)
                    ->setHttpStatusCode($httpStatusCode)
                    ->getResponse()
                    ->withStatus($httpStatusCode)
            );
        } catch (\Throwable $e) {
            Html::setHtmlTemplater(); // clear templater to defaults setting
            throw $e;
        } finally {
            if ($this->allowQuit) {
                exit;
            }
        }
    }

    private function getOutputProcessor(): OutputInterface
    {
        /** @var class-string<OutputInterface> $processor */
        foreach (self::PROCESSORS_MAP as $processor => $mimes) {
            foreach ($mimes as $mime) {
                if (stripos($this->request->getHeaderLine('Accept'), $mime) !== false) {
                    return new $processor($mime);
                }
            }
        }
        return new Html();
    }

    private function getStatusCode(\Throwable $error): int
    {
        $typeError = get_class($error);
        if (in_array($typeError, array_keys($this->errorsMap))) {
            return $this->errorsMap[$typeError]['statusCode'] ?? self::DEFAULT_STATUS_CODE;
        }

        return self::DEFAULT_STATUS_CODE;
    }

    /**
     * @param list<string> $loggerTypes
     */
    private function sendToLogger(\Throwable $error, array $loggerTypes = []): void
    {
        $typeError = get_class($error);

        if (in_array($typeError, array_keys($this->errorsMap))
            && array_key_exists('loggerType', $this->errorsMap[$typeError])
        ) {
            $loggerTypes = $this->errorsMap[$typeError]['loggerType'];
        }

        foreach ($loggerTypes as $loggerType) {
            if (method_exists($this->logger, $loggerType)) {
                $this->logger->$loggerType(sprintf("%s %s\n%s", $typeError, $error->getCode(), $error->getMessage()), [
                    'code' => $error->getCode(),
                    'line' => $error->getLine(),
                    'file' => $error->getFile(),
                ]);
            }
        }
    }

    /**
     * @param array<int, list<string>|array<string, list<string>>> $errorsMap
     */
    public function setErrorsMap(array $errorsMap): ErrorHandler
    {
        foreach ($errorsMap as $statusCode => $array) {
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    $this->errorsMap[$key]['statusCode'] = $statusCode;
                    $this->errorsMap[$key]['loggerType'] = (array)$value;
                    continue;
                }
               $this->errorsMap[$value]['statusCode'] = $statusCode;
            }
        }

        return $this;
    }


    /**
     * Catch Errors, Warning, etc
     * @throws \ErrorException
     * @throws \Throwable
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
     * @throws \Throwable
     */
    public function register(): void
    {
        // Handles throwable, echo output and exit.
        set_exception_handler(function (\Throwable $error): void {
            $this->handle($error);
        });

        // Handles PHP execution errors such as warnings and notices.
        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): bool {
                if (!(error_reporting() & $severity)) {
                    // This error code is not included in error_reporting.
                    return true;
                }

                if (self::isFatalError($severity)) {
                    throw new \ErrorException(
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
                throw new \ErrorException(
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
        ], true);
    }

    public function allowQuit(bool $value = true): ErrorHandler
    {
        $this->allowQuit = $value;
        return $this;
    }

    /**
     * @param array<int, list<string>> $mappingLoggerType
     */
    public function setMappingLoggerType(array $mappingLoggerType): ErrorHandler
    {
        $this->mappingLoggerType = $mappingLoggerType;
        return $this;
    }

}
