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

$errorHandlerMiddleware = new ErrorHandlerMiddleware($errorHandler);
```
