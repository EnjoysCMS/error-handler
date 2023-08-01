<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Json implements ErrorOutputInterface
{

    private ResponseInterface $response;

    public function __construct(
        private readonly Error $error,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->response = $responseFactory
            ->createResponse($this->error->getHttpStatusCode())
            ->withHeader('Content-Type', 'application/json');
    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(
            json_encode(
                [
                    'error' => [
                        'type' => $this->error->getType(),
                        'code' => $this->error->getCode(),
                        'message' => $this->error->getMessage()
                    ]
                ]
            )
        );
        return $this->response;
    }
}
