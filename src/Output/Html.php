<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\ErrorHandler;
use EnjoysCMS\ErrorHandler\View\SimpleHtmlView;
use EnjoysCMS\ErrorHandler\View\ViewInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Html implements ErrorOutputInterface
{

    /**
     * @var ViewInterface
     */
    private static $templater;
    private ResponseInterface $response;

    public function __construct(
        private \Throwable               $error,
        ResponseFactoryInterface $responseFactory,
        private int                      $httpStatusCode = ErrorHandler::DEFAULT_STATUS_CODE,
         ?string                          $mimeType = null)
    {
        $this->response = $responseFactory->createResponse($httpStatusCode);
        if (self::$templater === null) {
            self::$templater = new SimpleHtmlView();
        }


    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(self::$templater->getContent($this->error, $this->httpStatusCode));
        return $this->response;
    }

    public static function setHtmlTemplater(ViewInterface $temlater = null): void
    {
        self::$templater = $temlater;
    }


}
