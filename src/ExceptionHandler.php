<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler;


use EnjoysCMS\Core\Http\Emitter\EmitterInterface;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Image;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Svg;
use EnjoysCMS\ErrorHandler\Output\Xml;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    private const  PROCESSORS_MAP = [
        Json::class => ['application/json', 'text/json'],
        Html::class => ['text/html'],
        Xml::class => ['text/xml'],
        Plain::class => ['text/plain', 'text/css', 'text/javascript'],
        Svg::class => ['image/svg+xml'],
        Image::class => ['image/gif', 'image/jpeg', 'image/png', 'image/webp']
    ];

    /**
     * @deprecated
     */
    private bool $allowQuit = false;

    /**
     * @var array<int, list<string>>
     */
    private array $errorsMap = [];

    /**
     * @var array<array-key, list<string>>
     */
    private array $loggerTypeMap = [
        500 => ['error']
    ];

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly EmitterInterface $emitter,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ErrorLoggerInterface $logger
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(Throwable $error): void
    {
        try {
            $httpStatusCode = $this->getStatusCode($error);

            $this->logger->log(
                PhpError::fromThrowable($error),
                $this->getLogLevels($error, $httpStatusCode)
            );

            $response = $this->getErrorOutput($error, $httpStatusCode)
                ->getResponse();

            $this->emitter->emit($response);
        } catch (Throwable $e) {
            Html::setHtmlTemplater(); // clear templater to defaults setting
            throw $e;
        } finally {
            if ($this->allowQuit) {
                exit;
            }
        }
    }


    /**
     * @deprecated
     */
    public function allowQuit(bool $value = true): ExceptionHandler
    {
        $this->allowQuit = $value;
        return $this;
    }


    /**
     * @param array<int, list<string>> $errorsMap
     */
    public function setErrorsMap(array $errorsMap): ExceptionHandler
    {
        $this->errorsMap = $errorsMap;
        return $this;
    }


    private function getStatusCode(Throwable $error): int
    {
        foreach ($this->errorsMap as $statusCode => $stack) {
            if (in_array($error::class, $stack)) {
                return $statusCode;
            }
        }
        return self::DEFAULT_STATUS_CODE;
    }


    private function getErrorOutput(Throwable $error, int $httpStatusCode): ErrorOutputInterface
    {
        /** @var class-string<ErrorOutputInterface> $processor */
        foreach (self::PROCESSORS_MAP as $processor => $mimes) {
            foreach ($mimes as $mime) {
                if (stripos($this->request->getHeaderLine('Accept'), $mime) !== false) {
                    return new $processor(new Error($error, $httpStatusCode, $mime), $this->responseFactory);
                }
            }
        }
        return new Html(new Error($error, $httpStatusCode, 'text/html'), $this->responseFactory);
    }

    /**
     * @param array<array-key, list<string>> $loggerTypeMap
     */
    public function setLoggerTypeMap(array $loggerTypeMap): ExceptionHandler
    {
        $this->loggerTypeMap = $loggerTypeMap;
        return $this;
    }


    private function getLogLevels(Throwable $error, int $httpStatusCode): array|false
    {
        $typeError = get_class($error);



        if (array_key_exists($typeError, $this->loggerTypeMap)) {
            return $this->loggerTypeMap[$typeError];
        }

        return $this->loggerTypeMap[$httpStatusCode] ?? false;
    }
}
