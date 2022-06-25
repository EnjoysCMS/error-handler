<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Svg extends AbstractOutput implements OutputInterface
{
    public function getResponse(): ResponseInterface
    {
        $code = empty($this->getError()->getCode()) ? "" : "[{$this->getError()->getCode()}]";
        $this->response->getBody()->write(
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50" viewBox="0 0 200 50">
    <text x="20" y="30" font-family="sans-serif" title="{$this->getError()->getMessage()}">
        $code {$this->getType()}
    </text>
</svg>
SVG
        );
        return $this->response;
    }
}
