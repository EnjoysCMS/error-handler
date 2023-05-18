<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{

    public function testResponseJson()
    {
        $errorOutput = new Json(
            new Error(new \InvalidArgumentException('This is the Error message'), 500, 'application/json'),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', $response->getReasonPhrase());
        $this->assertSame(['application/json'], $response->getHeaders()['Content-Type']);

        $body = $response->getBody()->__toString();
        $this->assertSame('{"error":{"type":"InvalidArgumentException","code":0,"message":"This is the Error message"}}', $body);
    }
}
