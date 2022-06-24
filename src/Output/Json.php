<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use HttpSoft\Message\Response;
use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

final class Json extends AbstractOutput implements OutputInterface
{

    public function getResponse(): ResponseInterface
    {
        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
        ;
        $response->getBody()->write(
            json_encode(
                [
                    'error' => [
                        'type' => $this->getType(),
                        'code' => $this->getError()->getCode(),
                        'message' => $this->getError()->getMessage()
                    ]
                ]
            )
        );
        return $response;
    }
}
