<?php

namespace Tests\Webrtc\Signaling;

use PHPUnit\Framework\Attributes\CoversClass;
use Webrtc\Signaling\Request;
use PHPUnit\Framework\TestCase;
use Webrtc\Signaling\SignalingInterface;
use Webrtcsignaler\WebSocketMessage;
use Webrtcsignaler\RequestMessage;
use Webrtcsignaler\SignalMessage;
use Webrtc\SDP\RTCSessionDescription;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
    private function createFakeRequestMessage()
    {
        $payload = new SignalMessage();
        $payload->setSdp('v=0...');
        $payload->setType(1); // "offer"

        $request = new RequestMessage();
        $request->setId('client-123');
        $request->setPayload($payload);
        $request->setMessageId("123");

        return $request;
    }

    private function createFakeWebSocketMessage($request)
    {
        $message = new WebSocketMessage;
        $message->setRequest($request);

        return $message;
    }

    public function testGetClientId()
    {
        $signaling = $this->createMock(SignalingInterface::class);
        $requestMsg = $this->createFakeRequestMessage();
        $message = $this->createFakeWebSocketMessage($requestMsg);

        $request = new Request($signaling, $message, 999);

        $this->assertEquals('client-123', $request->getClientId());
    }

    public function testGetOffer()
    {
        $signaling = $this->createMock(SignalingInterface::class);
        $requestMsg = $this->createFakeRequestMessage();
        $message = $this->createFakeWebSocketMessage($requestMsg);

        $request = new Request($signaling, $message, 999);
        $offer = $request->getOffer();

        $this->assertInstanceOf(RTCSessionDescription::class, $offer);
        $this->assertEquals('v=0...', $offer->getSdp());
        $this->assertEquals('offer', $offer->getType());
    }

    public function testSendOffer()
    {
        $signaling = $this->createMock(SignalingInterface::class);
        $signaling->expects($this->once())->method('send');

        $requestMsg = $this->createFakeRequestMessage();
        $message = $this->createFakeWebSocketMessage($requestMsg);

        $request = new Request($signaling, $message, 888);

        $description = new RTCSessionDescription('v=0...', 'offer');
        $request->sendOffer($description);
    }

    public function testRespondError()
    {
        $signaling = $this->createMock(SignalingInterface::class);
        $signaling->expects($this->once())->method('send');

        $requestMsg = $this->createFakeRequestMessage();
        $message = $this->createFakeWebSocketMessage($requestMsg);

        $request = new Request($signaling, $message, 555);

        $request->respondError('Something went wrong', 500);
    }
}

