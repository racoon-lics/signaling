<?php

namespace Tests\Webrtc\Signaling\Adapters;

use PHPUnit\Framework\Attributes\CoversClass;
use Webrtc\Signaling\Adapters\WebSocketSignalingAdapter;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

#[CoversClass(WebSocketSignalingAdapter::class)]
class WebSocketSignalingAdapterTest extends TestCase
{
    public function testSendMessageToClient()
    {
        $adapter = new WebSocketSignalingAdapter();

        $mockConn = $this->createMock(FakeConnection::class);
        $mockConn->expects($this->once())->method('send')->with('hello');

        $reflection = new \ReflectionClass($adapter);
        $clients = $reflection->getProperty('clients');
        $clients->setAccessible(true);
        $clients->setValue($adapter, [123 => $mockConn]);

        $adapter->send(123, 'hello');
    }

    public function testOnOpenAddsClient()
    {
        $adapter = new WebSocketSignalingAdapter();
        $conn = new FakeConnection(456);

        $adapter->onOpen($conn);

        $reflection = new \ReflectionClass($adapter);
        $clients = $reflection->getProperty('clients');
        $clients->setAccessible(true);
        $this->assertArrayHasKey(456, $clients->getValue($adapter));
    }

    public function testOnCloseRemovesClient()
    {
        $adapter = new WebSocketSignalingAdapter();
        $conn = new FakeConnection(789);

        $adapter->onOpen($conn);
        $adapter->onClose($conn);

        $reflection = new \ReflectionClass($adapter);
        $clients = $reflection->getProperty('clients');
        $clients->setAccessible(true);
        $this->assertArrayNotHasKey(789, $clients->getValue($adapter));
    }

    public function testOnErrorClosesConnection()
    {
        $adapter = new WebSocketSignalingAdapter();
        $mockConn = $this->createMock(FakeConnection::class);

        $mockConn->expects($this->once())->method('close');

        $adapter->onError($mockConn, new \Exception('Error!'));
    }

    public function testOnMessageCallsCallback()
    {
        $adapter = new WebSocketSignalingAdapter();
        $conn = new FakeConnection(123);

        $called = false;
        $adapter->onOfferRequest(function ($clientId, $message) use (&$called) {
            $called = true;
            $this->assertEquals(123, $clientId);
            $this->assertEquals('test-message', $message);
        });

        $adapter->onMessage($conn, 'test-message');

        $this->assertTrue($called);
    }
}

