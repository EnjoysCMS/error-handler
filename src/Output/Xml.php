<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use Psr\Http\Message\ResponseInterface;

final class Xml extends AbstractErrorOutput implements ErrorOutputInterface
{
    public function getResponse(): ResponseInterface
    {

        $type = get_class($this->error);
        $this->response->getBody()->write(
            <<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>$type</type>
    <code>{$this->error->getCode()}</code>
    <message>{$this->error->getMessage()}</message>
</error>
XML
        );
        return $this->response;
    }
}
