<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\View\SimpleHtmlView;
use EnjoysCMS\ErrorHandler\View\ViewInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Html implements ErrorOutputInterface
{

    private static ?ViewInterface $templater = null;
    private ResponseInterface $response;

    public function __construct(
        private Error $error,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->response = $responseFactory->createResponse($this->error->getHttpStatusCode());
    }

    public function getResponse(): ResponseInterface
    {
        if (self::$templater === null) {
            self::$templater = new SimpleHtmlView();
        }

        $this->response->getBody()->write(
            self::$templater->getContent($this->error->getError(), $this->error->getHttpStatusCode())
        );
        return $this->response;
    }

    public static function setHtmlTemplater(ViewInterface $templater = null): void
    {
        self::$templater = $templater;
    }


}
