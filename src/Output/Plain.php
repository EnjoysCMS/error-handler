<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use Psr\Http\Message\ResponseInterface;

final class Plain extends AbstractErrorOutput implements ErrorOutputInterface
{

    public function getResponse(): ResponseInterface
    {
        $code = empty($this->error->getCode()) ? "" : "[{$this->error->getCode()}]";
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
