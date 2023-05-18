<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Svg implements ErrorOutputInterface
{

    private ResponseInterface $response;

    public function __construct(
        private Error $error,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->response = $responseFactory
            ->createResponse($this->error->getHttpStatusCode());
    }

    public function getResponse(): ResponseInterface
    {
        $code = empty($this->error->getError()->getCode()) ? "" : "[{$this->error->getError()->getCode()}]";
        $type = $this->error->getType();
        $this->response->getBody()->write(
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200">
    <text x="20" y="30" title="$type">
        $code $type
    </text>
    <text x="20" y="60"  title="{$this->error->getError()->getMessage()}">
        {$this->error->getError()->getMessage()}
    </text>

</svg>
SVG
        );
        return $this->response;
    }
}
