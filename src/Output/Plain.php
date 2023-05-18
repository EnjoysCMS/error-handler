<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Plain implements ErrorOutputInterface
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
        $this->response->getBody()->write(
            sprintf(
                "%s %s\n%s",
                empty($this->error->getCode()) ? "" : "[{$this->error->getCode()}]",
                $this->error->getType(),
                $this->error->getMessage()
            )
        );
        return $this->response;
    }
}
