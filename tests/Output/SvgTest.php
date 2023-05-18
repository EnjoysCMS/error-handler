<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Svg;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class SvgTest extends TestCase
{

    public function testResponseSvg()
    {
        $errorOutput = new Svg(
            new Error(new \InvalidArgumentException('This is the Error message'), 500),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', $response->getReasonPhrase());

        $body = $response->getBody()->__toString();
        $this->assertSame(
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200">
    <text x="20" y="30" title="InvalidArgumentException">
         InvalidArgumentException
    </text>
    <text x="20" y="60"  title="This is the Error message">
        This is the Error message
    </text>
</svg>
SVG,
            $body
        );
    }
}
