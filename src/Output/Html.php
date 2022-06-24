<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Html extends AbstractOutput implements OutputInterface
{

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(
            <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$this->getType()} {$this->getError()->getCode()}</title>
    <style>html{font-family: sans-serif;}</style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>{$this->getType()} {$this->getError()->getCode()}</h1>
    <p>{$this->getError()->getMessage()}</p>
</body>
</html>
HTML
        );
        return $this->response;
    }
}
