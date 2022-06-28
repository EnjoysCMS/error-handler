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
        $code = empty($this->getError()->getCode()) ? "" : "[{$this->getError()->getCode()}]";
        $type = get_class($this->error);

        $this->response->getBody()->write(
            sprintf(
                "%s %s\n%s",
                $code,
                $type,
                $this->error->getMessage()
            )
        );
        return $this->response;
    }
}
