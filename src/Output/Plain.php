<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Plain extends AbstractOutput implements OutputInterface
{

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(
            sprintf(
                "%s %s\n%s",
                $this->getType(),
                $this->getError()->getCode(),
                $this->getError()->getMessage()
            )
        );
        return $this->response;
    }
}
