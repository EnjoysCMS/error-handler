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
        $type = get_class($this->error);
        $this->response->getBody()->write(
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200">
    <text x="20" y="30" title="$type">
        $code $type
    </text>
    <text x="20" y="60"  title="{$this->error->getMessage()}">
        {$this->error->getMessage()}
    </text>

</svg>
SVG
        );
        return $this->response;
    }
}
