<?php
namespace Bankiru\MonologLogstash;

use Monolog\Logger;
use Monolog\TestCase;

class ZMQHandlerTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('ZMQ')) {
            $this->markTestSkipped("zmq extension not installed");
        }
    }

    /**
     * @covers Bankiru\MonologLogstash\ZMQHandler::__construct()
     */
    public function testConstructor()
    {
        $handler = new ZMQHandler('tcp://127.0.0.1:2120', true, array(), \ZMQ::SOCKET_PUSH, array(), Logger::DEBUG, true);
        $this->assertInstanceOf('Bankiru\MonologLogstash\ZMQHandler', $handler);
    }

    /**
     * @covers Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedException \Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage dsn is invalid
     */
    public function testConstructFailOnEmptyDsn()
    {
        new ZMQHandler('');
        $this->fail('ZMQHandler::__construct() should fail with empty dsn');
    }

    /**
     * @covers Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedException \Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage dsn is invalid
     */
    public function testConstructFailOnNonStringDsn()
    {
        new ZMQHandler(123);
        $this->fail('ZMQHandler::__construct() should fail with invalid dsn');
    }

    /**
     * @covers Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedException \Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage dsn has invalid schema
     */
    public function testConstructFailOnInvalidSchemaInDsn()
    {
        new ZMQHandler('http://127.0.0.1:2120');
        $this->fail('ZMQHandler::__construct() should fail with invalid schema in dsn');
    }

    /**
     * @covers Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedException \Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage no port specified in the dsn
     */
    public function testConstructFailOnEmptyPortInDsn()
    {
        new ZMQHandler('tcp://127.0.0.1');
        $this->fail('ZMQHandler::__construct() should fail without port in dsn');
    }

    /**
     * @covers       Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedExceptionMessage dsn has unexpected path or query
     * @dataProvider provideUnexpectedPathsOrQueries
     */
    public function testConstructFailOnUnexpectedPathsOrQueriesInDsn($dsnEnd)
    {
        $dsn = 'tcp://127.0.0.1:2120' . $dsnEnd;
        $this->setExpectedException('\Psr\Log\InvalidArgumentException', "dsn '{$dsn}' has unexpected path or query");
        new ZMQHandler($dsn);
        $this->fail('ZMQHandler::__construct() should fail with dsn: ' . $dsn);
    }

    /**
     * @covers       Bankiru\MonologLogstash\ZMQHandler::__construct()
     * @expectedException \Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage socketType is invalid
     * @dataProvider provideInvalidSocketTypes
     */
    public function testConstructFailOnInvalidSocketType($socketType)
    {
        new ZMQHandler('tcp://127.0.0.1:2120', false, array(), $socketType);
        $this->fail('ZMQHandler::__construct() should fail on invalid socketType: ' . var_export($socketType, true));
    }

    public function provideUnexpectedPathsOrQueries()
    {
        return array(
            array('/asdasd'),
            array('/?asd=1'),
            array('/#asdsad'),
            array('/asdasd?asd=dfgh'),
            array('/asdasd#fdgf'),
            array('/asdasd?asd=dfgh#fdgf'),
        );
    }


    public function provideInvalidSocketTypes()
    {
        return array(
            array('123747823'),
            array(null),
            array(-1),
            array('asd'),
        );
    }

    public function testClose()
    {
        $handler = new ZMQHandler('tcp://127.0.0.1:2120');

        $socket = $this->getSocketMock(array('disconnect'), null, \ZMQ::SOCKET_PUSH, false);

        $this->assertFalse($socket->isPersistent());

        $socket->expects($this->once())
            ->method('disconnect')
            ->will($this->returnSelf());

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refProp = $refClass->getProperty('socket');
        $refProp->setAccessible(true);
        $refProp->setValue($handler, $socket);
        $refProp->setAccessible(false);

        $handler->close();
    }

    public function testWrite()
    {
        $testMessage = uniqid('logmessage');

        $socket = $this->getSocketMock();

        $socket->expects($this->once())
            ->method('send')
            ->with($this->equalTo($testMessage), $this->equalTo(\ZMQ::MODE_DONTWAIT));

        $handler = $this->getHandlerMock(array('getSocket'));

        $handler->expects($this->once())
            ->method('getSocket')
            ->will($this->returnValue($socket));

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refMethod = $refClass->getMethod('write');
        $refMethod->setAccessible(true);

        $refMethod->invoke($handler, array('formatted' => $testMessage));

        $refMethod->setAccessible(false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteFailed()
    {
        $testMessage = uniqid('logmessage');

        $socket = $this->getSocketMock();

        $socket->expects($this->once())
            ->method('send')
            ->with($this->equalTo($testMessage), $this->equalTo(\ZMQ::MODE_DONTWAIT))
            ->will($this->throwException(new \ZMQSocketException('test')));

        $handler = $this->getHandlerMock(array('getSocket'));

        $handler->expects($this->once())
            ->method('getSocket')
            ->will($this->returnValue($socket));

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refMethod = $refClass->getMethod('write');
        $refMethod->setAccessible(true);

        $refMethod->invoke($handler, array('formatted' => $testMessage));

        $refMethod->setAccessible(false);
    }

    public function testGetContext()
    {
        $handler = new ZMQHandler('tcp://127.0.0.1:2120', false, array(1 => 1));

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refMethod = $refClass->getMethod('getContext');
        $refMethod->setAccessible(true);

        $this->assertInstanceOf('\ZMQContext', $refMethod->invoke($handler));

        $refMethod->setAccessible(false);
    }

    public function testGetSocket()
    {
        $dsn = 'tcp://127.0.0.1:2120';

        $socket = $this->getSocketMock(array('setSockOpt', 'connect'));

        $socket
            ->expects($this->once())
            ->method('setSockOpt')
            ->with($this->equalTo(\ZMQ::SOCKOPT_SNDTIMEO), $this->equalTo(200));

        $socket->expects($this->once())
            ->method('connect')
            ->with($this->equalTo($dsn));

        $context = $this->getContextMock(array(), $socket);

        $handler = $this->getHandlerMock(
            array('getContext', 'close'),
            $dsn, true, array(), \ZMQ::SOCKET_PUSH, array(\ZMQ::SOCKOPT_SNDTIMEO => 200)
        );

        $handler->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refMethod = $refClass->getMethod('getSocket');
        $refMethod->setAccessible(true);

        $refMethod->invoke($handler);

        $refMethod->setAccessible(false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetSocketFail()
    {
        $dsn = 'tcp://127.0.0.1:2120';

        $socket = $this->getSocketMock(array('setSockOpt', 'connect', 'disconnect'));

        $socket
            ->expects($this->once())
            ->method('setSockOpt')
            ->with($this->equalTo(\ZMQ::SOCKOPT_SNDTIMEO), $this->equalTo(200));

        $socket->expects($this->once())
            ->method('connect')
            ->with($this->equalTo($dsn))
            ->will($this->throwException(new \ZMQSocketException('test')));

        $socket->expects($this->once())
            ->method('disconnect');

        $context = $this->getContextMock(array(), $socket);

        $handler = $this->getHandlerMock(
            array('getContext', 'close'),
            $dsn, true, array(), \ZMQ::SOCKET_PUSH, array(\ZMQ::SOCKOPT_SNDTIMEO => 200)
        );

        $handler->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

        $refClass = new \ReflectionClass('Bankiru\MonologLogstash\ZMQHandler');
        $refMethod = $refClass->getMethod('getSocket');
        $refMethod->setAccessible(true);

        $refMethod->invoke($handler);

        $refMethod->setAccessible(false);
    }

    private function getSocketMock($methods = array(), \ZMQContext $context = null, $socketType = \ZMQ::SOCKET_PUSH, $persistent = true) {
        if ($context == null) {
            $context = new \ZMQContext(1, $persistent);
        }
        return $this->getMockBuilder('\ZMQSocket')
            ->setConstructorArgs(array($context, $socketType, $persistent))
            ->setMethods($methods)
            ->getMock();
    }

    private function getContextMock($methods = array(), \ZMQSocket $socket = null) {
        $context = $this->getMockBuilder('\ZMQContext')
            ->setConstructorArgs(array(1, $socket->isPersistent()))
            ->setMethods($methods)
            ->getMock();

        if ($socket) {
            $context->expects($this->any())
                ->method('getSocket')
                ->with(
                    $this->equalTo($socket->getSocketType()),
                    $socket->isPersistent() ? $this->isType('string') : $this->isNull(),
                    $this->isType('callable')
                )
                ->will($this->returnCallback(function ($socketType, $persistentId, $callback) use ($socket) {
                    call_user_func($callback, $socket);
                    return $socket;
                }));
        }

        return $context;
    }

    private function getHandlerMock($methods = array(), $dsn = 'tcp://127.0.0.1:2120', $persistent = true, $options = array(), $socketType = \ZMQ::SOCKET_PUSH, $socketOptions = array(),
                                    $level = Logger::DEBUG, $bubble = true) {

        return $this->getMockBuilder('Bankiru\MonologLogstash\ZMQHandler')
            ->setConstructorArgs(array($dsn, $persistent, $options, $socketType, $socketOptions, $level, $bubble))
            ->setMethods($methods)
            ->getMock();
    }

}
