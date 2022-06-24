<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Xml extends AbstractOutput implements OutputInterface
{
    public function getResponse(): ResponseInterface
    {

        $this->response->getBody()->write(
            <<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>{$this->getType()}</type>
    <code>{$this->getError()->getCode()}</code>
    <message>{$this->getError()->getMessage()}</message>
</error>
XML
        );
        return $this->response;
    }
}
