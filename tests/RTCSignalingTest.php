<?php

namespace Tests\Webrtc\Signaling;

use PHPUnit\Framework\Attributes\CoversClass;
use Webrtc\Signaling\RTCSignaling;
use PHPUnit\Framework\TestCase;
use Webrtc\Signaling\SignalingInterface;

#[CoversClass(RTCSignaling::class)]
class RTCSignalingTest extends TestCase
{
    public function testStartDelegates()
    {
        $adapter = $this->createMock(SignalingInterface::class);
        $adapter->expects($this->once())->method('start');

        $signaling = new RTCSignaling($adapter);
        $signaling->start();
    }

    public function testSendDelegates()
    {
        $adapter = $this->createMock(SignalingInterface::class);
        $adapter->expects($this->once())->method('send')->with('client1', 'hello');

        $signaling = new RTCSignaling($adapter);
        $signaling->send('client1', 'hello');
    }

    public function testOnOfferRequestDecoratesClosure()
    {
        $adapter = $this->createMock(SignalingInterface::class);
        $adapter->expects($this->once())->method('onOfferRequest')->with($this->isInstanceOf(\Closure::class));

        $signaling = new RTCSignaling($adapter);

        $signaling->onOfferRequest(function ($request) {
            $this->assertInstanceOf(\Webrtc\Signaling\Request::class, $request);
        });
    }
}
