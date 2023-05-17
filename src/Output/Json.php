<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\ErrorHandler;
use EnjoysCMS\ErrorHandler\View\SimpleHtmlView;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Json  implements ErrorOutputInterface
{

    public function __construct(
        private \Throwable               $error,
        ResponseFactoryInterface $responseFactory,
        private int                      $httpStatusCode = ErrorHandler::DEFAULT_STATUS_CODE,
        ?string                          $mimeType = null)
    {
        $this->response = $responseFactory->createResponse($this->httpStatusCode);
    }
    public function getResponse(): ResponseInterface
    {
        $type = get_class($this->error);
        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
        ;
        $response->getBody()->write(
            json_encode(
                [
                    'error' => [
                        'type' => $type,
                        'code' => $this->error->getCode(),
                        'message' => $this->error->getMessage()
                    ]
                ]
            )
        );
        return $response;
    }
}
