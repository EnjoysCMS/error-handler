<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Xml implements ErrorOutputInterface
{
    private ResponseInterface $response;

    public function __construct(
        private Error $error,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->response = $responseFactory
            ->createResponse($this->error->getHttpStatusCode());
    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>{$this->error->getType()}</type>
    <code>{$this->error->getError()->getCode()}</code>
    <message>{$this->error->getError()->getMessage()}</message>
</error>
XML
        );
        return $this->response;
    }
}
