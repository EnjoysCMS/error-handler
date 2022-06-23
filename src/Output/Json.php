<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Json
{
    private Error $error;

    public function setError(Error $error)
    {
        $this->error = $error;
    }

    public function getResponse(): ResponseInterface
    {
        $response = new Response(500);
        $response->getBody()->write(json_encode(
            $this->error->__toString()
        ));
        return $response;
    }
}
