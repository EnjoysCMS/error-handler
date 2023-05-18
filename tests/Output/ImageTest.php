<?php

namespace EnjoysCMS\Tests\ErrorHandler\Output;

use EnjoysCMS\ErrorHandler\Error;
use EnjoysCMS\ErrorHandler\Output\Image;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{

    public function dataForTestResponseImage()
    {
        return [
          ['image/gif', 'image/gif'],
          ['image/jpeg', 'image/jpeg'],
          ['image/png', 'image/png'],
          ['image/webp', 'image/webp']
        ];
    }

    /**
     * @dataProvider dataForTestResponseImage
     */
    public function testResponseImage($expectContentType, $acceptMimeType)
    {
        $errorOutput = new Image(
            new Error(new \InvalidArgumentException('This is the Error message'), 500, $acceptMimeType),
            new ResponseFactory()
        );
        $response = $errorOutput->getResponse();
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', $response->getReasonPhrase());
        $this->assertSame([$expectContentType], $response->getHeaders()['Content-Type']);

        $body = $response->getBody()->__toString();
        $this->assertNotEmpty($body);
    }
}
