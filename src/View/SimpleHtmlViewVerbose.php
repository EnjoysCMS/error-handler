<?php

declare(strict_types=1);

namespace EnjoysCMS\ErrorHandler\View;

use EnjoysCMS\ErrorHandler\ErrorHandler;
use HttpSoft\Message\Response;

final class SimpleHtmlViewVerbose implements ViewInterface
{
    public function getContent(\Throwable $error, int $statusCode = ErrorHandler::DEFAULT_STATUS_CODE): string
    {
        /** @var string $phrase */
        $phrase = $this->getPhrase($statusCode);
        $type = get_class($error);

        $message = implode(
            ': ',
            array_filter(
                [
                    $type,
                    empty($error->getCode()) ? null : $error->getCode(),
                    empty($error->getMessage()) ? null : htmlspecialchars($error->getMessage()),
                ],
                function ($item) {
                    return !is_null($item);
                }
            )
        );

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Error $statusCode. $phrase</title>
    <style>
        body {

            margin: 0 1em;
            font-family: Tahoma, Verdana, Arial, sans-serif;
        }

        code {
            font-size: 120%;
        }
    </style>
</head>
<body>
<h1>An error occurred.</h1>
<p>Sorry, the page you are looking for is currently unavailable.<br/>
    Please try again later.</p>
<p>If you are the system administrator of this resource then you should check
    the error log for details.</p>
<p>
    <code><b>$statusCode</b><br>$phrase
    </code>
    <code style="display: block; margin-top: 2em; color: grey">
    $message
    </code>
</p>
<p>
HTML;
    }

    /**
     * @param int $statusCode
     * @return mixed|string
     */
    private function getPhrase(int $statusCode): mixed
    {
        $reflection = new \ReflectionClass(Response::class);
        $phrases = $reflection->getProperty('phrases');
        $phrases->setAccessible(true);
        return $phrases->getValue()[$statusCode] ?? 'Unknown error';
    }
}
