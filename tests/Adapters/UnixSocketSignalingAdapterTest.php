<?php

namespace Tests\Webrtc\Signaling\Adapters;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;
use Webrtc\Signaling\Adapters\UnixSocketSignalingAdapter;

#[CoversClass(UnixSocketSignalingAdapter::class)]
class UnixSocketSignalingAdapterTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $adapter = new UnixSocketSignalingAdapter('/tmp/test.sock', 0, false);
        $this->assertInstanceOf(UnixSocketSignalingAdapter::class, $adapter);
    }

    public function testSendMockedConnection()
    {
        $adapter = new UnixSocketSignalingAdapter();
        $reflection = new \ReflectionClass($adapter);
        $clients = $reflection->getProperty('clients');

        $mockConn = $this->createMock(ConnectionInterface::class);
        $mockConn->expects($this->once())->method('write')->with('hello');

        $clientId = 'clientX';
        $clients->setValue($adapter, [$clientId => $mockConn]);

        $adapter->send($clientId, 'hello');
    }
}

