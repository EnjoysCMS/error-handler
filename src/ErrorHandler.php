<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use Doctrine\ORM\NoResultException;
use EnjoysCMS\Core\Exception\ForbiddenException;
use EnjoysCMS\Core\Exception\NotFoundException;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Image;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Svg;
use EnjoysCMS\ErrorHandler\Output\Xml;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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

    private const DEFAULT_STATUS_CODE = 500;

    private array $mappingCode = [];
    private array $errorsMap = [];
    private array $mappingLoggerType = [];
    private EmitterInterface $emitter;
    private LoggerInterface $logger;
    private ?Error $error = null;
    private ?ServerRequestInterface $request = null;


    public function __construct(LoggerInterface $logger = null, EmitterInterface $emitter = null)
    {
        $this->logger = $logger ?? new \Psr\Log\NullLogger();
        $this->emitter = $emitter ?? new SapiEmitter();
    }

    public function handle(\Throwable $error, ServerRequestInterface $request): void
    {
        $typeError = get_class($error);
        $httpStatusCode = $this->getStatusCode($typeError);

        $output = $this->getOutputProcessor($request);


        $this->emitter->emit(
            $output
                ->setError($error)
                ->getResponse()
                ->withStatus($httpStatusCode)
        );
        exit;
    }


    private function getOutputProcessor(ServerRequestInterface $request)
    {
        foreach (self::PROCESSORS_MAP as $processor => $mimes) {
            foreach ($mimes as $mime) {
                if (stripos($request->getHeaderLine('Accept'), $mime) !== false) {
                    return new $processor($mime);
                }
            }
        }

        return new Html();
    }

    private function getStatusCode(string $typeError)
    {
        if (in_array($typeError, array_keys($this->getErrorsMap()))) {
            return $this->getErrorsMap()[$typeError]['statusCode'] ?? self::DEFAULT_STATUS_CODE;
        }

        return self::DEFAULT_STATUS_CODE;
    }


    public function setErrorsMap(array $errorsMap)
    {
        foreach ($errorsMap as $statusCode => $array) {
            foreach ($array as $key => $value) {
                if (is_array($value)){
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
}
