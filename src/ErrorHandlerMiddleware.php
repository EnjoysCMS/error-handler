<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class ErrorHandlerMiddleware  implements MiddlewareInterface
{

    public function __construct(private readonly ExceptionHandlerInterface $exceptionHandler)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $error) {
            $this->exceptionHandler->handle($error);
            exit;
        }
    }

}
