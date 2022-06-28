### ErrorHandler

```php 
$errorHandler = new errorHandler(logger: ?LoggerInterface, emitter: ?EmitterInterface);

// Все ошибки будут выводится с http статусом 500, с помощью setErrorsMap() можно переопределить статусы ошибок.
// и заодно определить какие ошибки передавать в logger
$errorHandler->setErrorsMap([
    404 => [
        // Если название класса установлено в качестве ключа, а значения string[], то будет вызван logger
        // с указанными методами
        NotFoundException::class => [
            'info', 'warning'
        ],
        
        // logger вызван не будет
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


try {
    // ... something code
} catch(\Throwable $error) {
    $errorHandler->handle($error, Psr\Http\Message\ServerRequestInterface $request);
}


```

### ErrorHandlerMiddleware
```php 
$errorHandler = new errorHandler();
// ... more setting error handler
$errorHandlerMiddleware = new ErrorHandlerMiddleware($errorHandler);
```
