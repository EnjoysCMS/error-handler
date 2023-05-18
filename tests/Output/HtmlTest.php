<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Plain;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{

    public function testResponseHtml()
    {
        $errorOutput = new Html(
            new Error(new \InvalidArgumentException('This is the Error message'), 500),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', $response->getReasonPhrase());

        $body = $response->getBody()->__toString();
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('<title>Error 500. Internal Server Error</title>', $body);
    }

}
