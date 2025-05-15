<?php

namespace Tests\Webrtc\Signaling\Adapters;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use React\Stream\WritableStreamInterface;
use Webrtc\Signaling\Adapters\TcpSignalingAdapter;

#[CoversClass(TcpSignalingAdapter::class)]
class TcpSignalingAdapterTest extends TestCase
{
    public function testCanBeInstantiatedAsServer()
    {
        $adapter = new TcpSignalingAdapter('127.0.0.1', 9000, false);
        $this->assertInstanceOf(TcpSignalingAdapter::class, $adapter);
    }

    public function testCanBeInstantiatedAsClient()
    {
        $adapter = new TcpSignalingAdapter('127.0.0.1', 9000, true);
        $this->assertInstanceOf(TcpSignalingAdapter::class, $adapter);
    }

    public function testSendAndReceiveMocked()
    {
        $adapter = new TcpSignalingAdapter('127.0.0.1', 9000);
        $reflection = new \ReflectionClass($adapter);
        $clients = $reflection->getProperty('clients');

        $mockStream = $this->createMock(WritableStreamInterface::class);
        $mockStream->expects($this->once())->method('write')->with('test');

        $clientId = 'mock123';
        $clients->setValue($adapter, [$clientId => $mockStream]);

        $adapter->send($clientId, 'test');
    }
}
