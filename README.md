### ErrorHandler

```php
$exceptionHandler = new \EnjoysCMS\ErrorHandler\ExceptionHandler(
    request: $request, //required, Psr\Http\Message\ServerRequestInterface::class
    emitter: $emitter, //required, EnjoysCMS\Core\Interfaces\EmitterInterface::class
    responseFactory: $responseFactory, //required, Psr\Http\Message\ResponseFactoryInterface::class
    logger: $logger,  // \EnjoysCMS\ErrorHandler\ErrorLoggerInterface::class or null
);

// Все ошибки будут выводиться с http статусом 500, с помощью setErrorsMap() можно переопределить статусы ошибок.
// По-умолчанию в logger передаваться ничего не будет.
$exceptionHandler->setErrorsMap([
    404 => [
        NotFoundException::class,
        NoResultException::class,
        //...
    ],
    403 => [
        ForbiddenException::class
    ]
]);

// Установка другого шаблона для вывода ошибок в HTML
\EnjoysCMS\ErrorHandler\Output\Html::setHtmlTemplater(
    // Реализация \EnjoysCMS\ErrorHandler\View\ViewInterface
    // уже внутри реализации можно настроить разные шаблоны под 
    // разные ошибки, например для 404 и 403
    new \EnjoysCMS\ErrorHandler\View\SimpleHtmlViewVerbose()
);

// Для передачи ошибки в logger, необходимо сопоставить ошибки с уровнем лога при помощи setLoggerTypeMap()
$exceptionHandler->setLoggerTypeMap([
    // для конкретно этой ошибки, будет вызван $logger->info()
    NoResultException::class => ['info'], 
    // для конкретно этой ошибки, будет вызван $logger->debug() и $logger->warning()
    NotFoundException::class => ['debug', 'warning'], 
    // для всех ошибок со статусом 500 будет вызван $logger->error()
    500 => ['error'], 
    // эта ошибка со статусом 500 вызвать $logger->error() НЕ будет, но будет вызван $logger->critical()
    \InvalidArgumentException::class => ['critical'], 
    //...
]);


try {
    // ... something code
} catch(\Throwable $error) {
    $exceptionHandler->handle($error);
}

```

### ErrorHandlerMiddleware

```php
$exceptionHandler = new \EnjoysCMS\ErrorHandler\ExceptionHandler();
// ... more setting error handler
$errorHandlerMiddleware = new \EnjoysCMS\ErrorHandler\ErrorHandlerMiddleware($exceptionHandler);
```
