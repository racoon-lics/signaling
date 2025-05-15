<?php

namespace Tests\Webrtc\Signaling\Adapters;

use PHPUnit\Framework\Attributes\CoversClass;
use Webrtc\Signaling\Adapters\UdpSignalingAdapter;
use PHPUnit\Framework\TestCase;
use React\Datagram\Socket;

#[CoversClass(UdpSignalingAdapter::class)]
class UdpSignalingAdapterTest extends TestCase
{
    public function testInstantiationClientAndServer()
    {
        $client = new UdpSignalingAdapter('127.0.0.1', 9001, true);
        $server = new UdpSignalingAdapter('127.0.0.1', 9001, false);

        $this->assertInstanceOf(UdpSignalingAdapter::class, $client);
        $this->assertInstanceOf(UdpSignalingAdapter::class, $server);
    }

    public function testSendUsesSocket()
    {
        $adapter = new UdpSignalingAdapter();
        $reflection = new \ReflectionClass($adapter);
        $socketProp = $reflection->getProperty('socket');

        $mockSocket = $this->createMock(Socket::class);
        $mockSocket->expects($this->once())->method('send')->with('msg', '127.0.0.1:9001');

        $socketProp->setValue($adapter, $mockSocket);

        $adapter->send('127.0.0.1:9001', 'msg');
    }
}

