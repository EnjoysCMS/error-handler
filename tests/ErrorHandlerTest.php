<?php

namespace EnjoysCMS\Tests\ErrorHandler;

use EnjoysCMS\Core\Interfaces\EmitterInterface;
use EnjoysCMS\ErrorHandler\ErrorHandler;
use EnjoysCMS\ErrorHandler\Output\ErrorOutputInterface;
use EnjoysCMS\ErrorHandler\Output\Html;
use EnjoysCMS\ErrorHandler\Output\Image;
use EnjoysCMS\ErrorHandler\Output\Json;
use EnjoysCMS\ErrorHandler\Output\Plain;
use EnjoysCMS\ErrorHandler\Output\Svg;
use EnjoysCMS\ErrorHandler\Output\Xml;
use Exception;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

class ErrorHandlerTest extends TestCase
{
    /**
     * getPrivateMethod
     *
     * @param class-string<object>|object $className
     * @param string $methodName
     * @return    ReflectionMethod
     * @throws ReflectionException
     * @author    Joe Sexton <joe@webtipblog.com>
     */
    public function getPrivateMethod($className, string $methodName): ReflectionMethod
    {
        $reflector = new ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * getPrivateProperty
     *
     * @param class-string<object>|object $className
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws ReflectionException
     * @author    Joe Sexton <joe@webtipblog.com>
     */
    public function getPrivateProperty($className, string $propertyName): ReflectionProperty
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * @throws Throwable
     */
    public function testHandle(): void
    {
        $emitter = $this->createMock(EmitterInterface::class);
        $emitter->expects($this->once())->method('emit');

        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/'),
            emitter: $emitter,
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $error = new \InvalidArgumentException('This is the Error message');
        $errorHandler->handle($error);
    }

    public function testErrorOutput_HTML(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/'),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 500
        ]);
        $this->assertInstanceOf(Html::class, $errorOutput);
    }

    public function testErrorOutput_JSON(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'application/json'
            ),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 500
        ]);
        $this->assertInstanceOf(Json::class, $errorOutput);
    }

    public function testErrorOutput_SVG(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'image/svg+xml'
            ),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 500
        ]);
        $this->assertInstanceOf(Svg::class, $errorOutput);
    }

    public function testErrorOutput_IMAGE(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'image/gif'
            ),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 500
        ]);
        $this->assertInstanceOf(Image::class, $errorOutput);
    }

    public function testErrorOutput_PLAINTEXT(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'text/plain'
            ),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 404
        ]);
        $this->assertInstanceOf(Plain::class, $errorOutput);
    }

    public function testErrorOutput_XML(): void
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'text/xml'
            ),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $method = $this->getPrivateMethod($errorHandler, 'getErrorOutput');
        $errorOutput = $method->invokeArgs($errorHandler, [
            'error' => new \InvalidArgumentException('This is the Error message'),
            'httpStatusCode' => 500
        ]);
        $this->assertInstanceOf(Xml::class, $errorOutput);
    }

    public function dataForTestGetStatusCode()
    {
        return [
          [500, [], \InvalidArgumentException::class],
          [400, [400=>[\InvalidArgumentException::class]], \InvalidArgumentException::class],
          [500, [400=>[\Exception::class]], \InvalidArgumentException::class],
          [404, [404=>[\Exception::class]], \Exception::class],
        ];
    }


    /**
     * @dataProvider dataForTestGetStatusCode
     */
    public function testGetStatusCode($expect, $errorsMap, $exceptionClassString)
    {
        $errorHandler = new ErrorHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/'),
            emitter: $this->createMock(EmitterInterface::class),
            responseFactory: new ResponseFactory(),
            logger: new NullLogger()
        );

        $errorHandler->setErrorsMap($errorsMap);

        $method = $this->getPrivateMethod($errorHandler, 'getStatusCode');
        $statusCode= $method->invokeArgs($errorHandler, [
            'error' => new $exceptionClassString('This is the Error message')
        ]);
        $this->assertSame($expect, $statusCode);
    }


}
