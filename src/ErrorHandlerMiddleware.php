<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\NoResultException;
use EnjoysCMS\Core\Components\Helpers\Error;
use EnjoysCMS\Core\Exception\ForbiddenException;
use EnjoysCMS\Core\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class ErrorHandlerMiddleware  implements MiddlewareInterface
{

    public function __construct(private ErrorHandlerInterface $errorHandler)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $error) {
            $this->errorHandler->setRequest($request);
            $this->errorHandler->handle($error);
        }
    }

}
