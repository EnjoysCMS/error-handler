<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\Xml;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{

    public function testXmlResponse()
    {
        $errorOutput = new Xml(
            new Error(new \InvalidArgumentException('This is the Error message'), 500),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', $response->getReasonPhrase());

        $body = $response->getBody()->__toString();
        $this->assertSame(
            <<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>InvalidArgumentException</type>
    <code>0</code>
    <message>This is the Error message</message>
</error>
XML,
            $body
        );
    }
}
