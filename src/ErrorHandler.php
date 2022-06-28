<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Image;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Svg;
use EnjoysCMS\ErrorHandler\Output\Xml;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

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

    private array $mappingCode = [];
    private array $errorsMap = [];
    private array $mappingLoggerType = [];
    private EmitterInterface $emitter;
    private LoggerInterface $logger;
    private ?Error $error = null;


    public function __construct(
        private ServerRequestInterface $request,
        LoggerInterface $logger = null,
        EmitterInterface $emitter = null
    ) {
        $this->logger = $logger ?? new \Psr\Log\NullLogger();
        $this->emitter = $emitter ?? new SapiEmitter();
    }

    public function handle(\Throwable $error): void
    {
        // disable error capturing to avoid recursive errors while handling exceptions
        $this->unregister();

        $httpStatusCode = $this->getStatusCode($error);
        $this->sendToLogger($error);

        $output = $this->getOutputProcessor();
        $this->emitter->emit(
            $output
                ->setError($error)
                ->setHttpStatusCode($httpStatusCode)
                ->getResponse()
                ->withStatus($httpStatusCode)
        );

        exit;
    }

    private function getOutputProcessor()
    {
        foreach (self::PROCESSORS_MAP as $processor => $mimes) {
            foreach ($mimes as $mime) {
                if (stripos($this->request->getHeaderLine('Accept'), $mime) !== false) {
                    return new $processor($mime);
                }
            }
        }
        return new Html();
    }

    private function getStatusCode(\Throwable $error)
    {
        $typeError = get_class($error);
        if (in_array($typeError, array_keys($this->getErrorsMap()))) {
            return $this->getErrorsMap()[$typeError]['statusCode'] ?? self::DEFAULT_STATUS_CODE;
        }

        return self::DEFAULT_STATUS_CODE;
    }

    private function sendToLogger(\Throwable $error)
    {
        $loggerTypes = [];
        $typeError = get_class($error);
        if (in_array($typeError, array_keys($this->getErrorsMap()))) {
            $loggerTypes = $this->getErrorsMap()[$typeError]['loggerType'] ?? [];
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

    public function setErrorsMap(array $errorsMap)
    {
        foreach ($errorsMap as $statusCode => $array) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $this->errorsMap[$key]['statusCode'] = $statusCode;
                    $this->errorsMap[$key]['loggerType'] = $value;
                    continue;
                }
                $this->errorsMap[$value]['statusCode'] = $statusCode;
            }
        }
    }

    public function getErrorsMap(): array
    {
        return $this->errorsMap;
    }

    /**
     * Catch Errors, Warning, etc
     * @return void
     * @throws \ErrorException
     */
    public function catchErrors()
    {
        $this->register();
    }

    /**
     * Register this error handler.
     */
    public function register(): void
    {
        ini_set('display_errors', '0');

        $logger = $this->logger;
        // Handles throwable, echo output and exit.
        set_exception_handler(function (\Throwable $error): void {
            $this->handle($error);
        });

        // Handles PHP execution errors such as warnings and notices.
        set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($logger): bool {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting.
                return true;
            }

            $error = new \ErrorException(
                sprintf('%s: %s', self::ERROR_NAMES[$severity] ?? '', $message),
                0,
                $severity,
                $file,
                $line
            );

            if (self::isFatalError($severity)) {
                throw $error;
            }

            $logger->debug($error->getMessage(), $error->getTrace());
            return true;
        });

        // Handles fatal error.
        register_shutdown_function(function (): void {
            $e = error_get_last();

            if ($e !== null && self::isFatalError($e['type'])) {
                $error = new \ErrorException(
                    sprintf('%s: %s', self::ERROR_NAMES[$e['type']] ?? '', $e['message']),
                    0,
                    $e['type'],
                    $e['file'],
                    $e['line']
                );
                dd($error);
                $this->handle($error);
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


    public static function isFatalError($severity)
    {
        return $severity > 0 && in_array($severity, [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING,
                E_USER_ERROR,
            ], true);
    }
}
