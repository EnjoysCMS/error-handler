<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Plain;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class PlainTest extends TestCase
{

    public function testResponsePlain()
    {
        $errorOutput = new Plain(
            new Error(new \InvalidArgumentException('This is the Error message'), 404),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());

        $body = $response->getBody()->__toString();
        $this->assertSame("InvalidArgumentException\nThis is the Error message", $body);
    }

}
