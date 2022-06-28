<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\View\SimpleHtmlView;
use EnjoysCMS\ErrorHandler\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;

final class Html extends AbstractOutput implements OutputInterface
{

    /**
     * @var ViewInterface
     */
    static private $templater;

    public function __construct()
    {
        parent::__construct();

        if (self::$templater === null) {
            self::$templater = new SimpleHtmlView();
        }
    }

    public function getResponse(): ResponseInterface
    {
        $this->response->getBody()->write(self::$templater->getContent($this->error, $this->httpStatusCode));
        return $this->response;
    }

    static public function setHtmlTemplater(ViewInterface $temlater = null)
    {
        self::$templater = $temlater;
    }


}
