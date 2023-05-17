<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use Psr\Http\Message\ResponseInterface;

interface ErrorOutputInterface
{
    public function getResponse(): ResponseInterface;
}
