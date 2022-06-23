<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use Doctrine\ORM\NoResultException;
use EnjoysCMS\Core\Exception\ForbiddenException;
use EnjoysCMS\Core\Exception\NotFoundException;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Xml;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class ErrorHandler implements ErrorHandlerInterface
{
    private array $mappingCode = [];
    private array $mappingLoggerType = [];
    private EmitterInterface $emitter;
    private LoggerInterface $logger;
    private ?Error $error = null;
    private ?ServerRequestInterface $request = null;

    private int $httpStatus = 500;

    public function __construct(LoggerInterface $logger = null, EmitterInterface $emitter = null)
    {
        $this->logger = $logger ?? new \Psr\Log\NullLogger();
        $this->emitter = $emitter ?? new SapiEmitter();
    }

    public function handle(\Throwable $error): void
    {
        $this->error = new Error($error);
        $this->httpStatus = $this->getHttpStatus();
        $this->sendToLogger();

        $output = $this->getOutputProcessor();
        $output->setError($this->error);

        $this->emitter->emit($output->getResponse()->withStatus($this->httpStatus));
        exit;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    private function getHttpStatus(): int
    {
        foreach ($this->mappingCode as $httpStatus => $stack) {
            if (in_array($this->type, $stack)) {
                return $httpStatus;
            }
        }

        return $this->httpStatus;
    }

    private function getOutputProcessor()
    {
        if ($this->request === null){
            throw new \InvalidArgumentException('Set request!');
        }

        $processorMap = [
            Json::class => ['application/json'],
            Html::class => ['text/html'],
            Xml::class => ['text/xml'],
            Plain::class => ['text/plain', 'text/css', 'text/javascript'],
        ];

        foreach ($processorMap as $processor => $types) {
            foreach ($types as $type) {
                if (stripos($this->request->getHeaderLine('Content-Type'), $type) !== false) {
                    return new $processor;
                }
            }
        }

        return new Html();
    }

    private function sendToLogger()
    {
        $loggerTypes = array_keys(
            array_filter($this->getMappingLoggerType(), function ($stack, $type) {
                if (in_array($this->error->getType(), $stack)) {
                    return $type;
                }
            }, ARRAY_FILTER_USE_BOTH)
        );

        foreach ($loggerTypes as $loggerType) {
            $this->logger->$loggerType($this->error, [
                'file' => $this->error->getError()->getFile(),
                'line' => $this->error->getError()->getLine(),
                'code' => $this->error->getError()->getCode(),
            ]);
        }
    }

    public function setMappingCode(array $mappingCode)
    {
        $this->mappingCode = $mappingCode;
    }

    public function getMappingCode(): array
    {
        return $this->mappingCode;
    }

    public function setMappingLoggerType(array $mappingLoggerType)
    {
        $this->mappingLoggerType = $mappingLoggerType;
    }

    public function getMappingLoggerType(): array
    {
        return $this->mappingLoggerType;
    }
}
