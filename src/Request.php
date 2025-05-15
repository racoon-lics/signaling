<?php

/**
 * This file is part of the PHP WebRTC package.
 *
 * (c) Amin Yazdanpanah <https://www.aminyazdanpanah.com/#contact>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webrtc\Signaling;

use Ratchet\RFC6455\Messaging\Frame;
use Webrtc\SDP\RTCSessionDescription;
use Webrtc\Signaling\Adapters\WebSocketSignalingAdapter;
use Webrtcsignaler\RequestMessage;
use Webrtcsignaler\ResponseMessage;
use Webrtcsignaler\SignalMessage;
use Webrtcsignaler\WebSocketMessage;

/**
 * WebRTC Signaling Request Handler
 *
 * This class encapsulates a WebRTC signaling request and provides methods
 * to process and respond to WebSocket signaling messages. It handles the
 * complete request/response cycle for WebRTC offer/answer exchange.
 *
 * Features:
 * - Parses and validates incoming WebRTC signaling requests
 * - Manages SDP offer/answer exchange
 * - Provides response generation for both success and error cases
 * - Handles WebSocket message framing when needed
 * - Supports all standard SDP types (offer, answer, pranswer, rollback)
 *
 * Usage:
 * - Processes incoming signaling requests from WebSocket connections
 * - Generates appropriate responses for SDP exchanges
 * - Handles error conditions with proper status codes
 *
 * Message Flow:
 * 1. Receives WebSocketMessage containing signaling request
 * 2. Extracts SDP and metadata from request
 * 3. Provides methods to generate proper responses
 * 4. Handles message serialization and WebSocket framing
 *
 * @package Webrtc\Signaling
 */
class Request
{
    private const array SDP_TYPE = [
        "unknown",
        "offer",
        "answer",
        "pranswer",
        "rollback"
    ];
    private ?RequestMessage $request;
    private ?SignalMessage $payload;

    public function __construct(
        private readonly SignalingInterface $signaling,
        private readonly WebSocketMessage   $message,
        private readonly int                $webSocketId
    )
    {
        $this->request = $message->getRequest();
        $this->payload = $this->request->getPayload();
    }

    /**
     * Get the client ID associated with the request.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->request->getId();
    }

    /**
     * Get the offer from the request payload.
     *
     * @return RTCSessionDescription
     */
    public function getOffer(): RTCSessionDescription
    {
        return new RTCSessionDescription($this->payload->getSdp(), self::SDP_TYPE[$this->payload->getType()]);
    }

    /**
     * Get the WebSocket connection ID.
     *
     * @return int
     */
    public function getWebSocketId(): int
    {
        return $this->webSocketId;
    }

    /**
     * Get the full WebSocket message object.
     *
     * @return WebSocketMessage
     */
    public function getMessage(): WebSocketMessage
    {
        return $this->message;
    }

    /**
     * Send an offer back to the client.
     *
     * @param RTCSessionDescription $description
     * @return void
     */
    public function sendOffer(RTCSessionDescription $description): void
    {
        $message = $this->createSignalMessage($description->getSdp(), array_flip(self::SDP_TYPE)[$description->getType()]);
        $responseMessage = $this->createSuccessResponseMessage($message);
        $responseWebSocketMessage = $this->createWebSocketResponseMessage($responseMessage);
        $serializedResponse = $responseWebSocketMessage->serializeToString();

        if ($this->signaling instanceof WebSocketSignalingAdapter) {
            $serializedResponse = new Frame($serializedResponse, true, Frame::OP_BINARY);
        }

        $this->signaling->send($this->webSocketId, $serializedResponse);
    }

    /**
     * Respond to the client with an error message.
     *
     * @param string $message
     * @param int $errorCode
     * @return void
     */
    public function respondError(string $message, int $errorCode = 403): void
    {
        $responseMessage = $this->createErrorResponseMessage($message, $errorCode);
        $responseWebSocketMessage = $this->createWebSocketResponseMessage($responseMessage);
        $serializedResponse = $responseWebSocketMessage->serializeToString();

        $this->signaling->send($this->webSocketId, new Frame($serializedResponse, true, Frame::OP_BINARY));
    }

    /**
     * Create a new SignalMessage.
     *
     * @param string $sdp
     * @param int $type
     * @return SignalMessage
     */
    public function createSignalMessage(string $sdp, int $type): SignalMessage
    {
        $signalMessage = new SignalMessage();
        $signalMessage->setSdp($sdp);
        $signalMessage->setType($type);

        return $signalMessage;
    }

    /**
     * Create a successful ResponseMessage based on a SignalMessage.
     *
     * @param SignalMessage $message
     * @return ResponseMessage
     */
    public function createSuccessResponseMessage(SignalMessage $message): ResponseMessage
    {
        $responseMessage = new ResponseMessage();
        $responseMessage->setMessageId($this->request->getMessageId());
        $responseMessage->setStatusCode(200);
        $responseMessage->setResponse($message);

        return $responseMessage;
    }

    /**
     * Create a WebSocketMessage wrapping a ResponseMessage.
     *
     * @param ResponseMessage $responseMessage
     * @return WebSocketMessage
     */
    public function createWebSocketResponseMessage(ResponseMessage $responseMessage): WebSocketMessage
    {
        $webSocketMessage = new WebSocketMessage();
        $webSocketMessage->setResponse($responseMessage);

        return $webSocketMessage;
    }

    /**
     * Create an error ResponseMessage.
     *
     * @param string $message
     * @param int $errorCode
     * @return ResponseMessage
     */
    private function createErrorResponseMessage(string $message, int $errorCode): ResponseMessage
    {
        $responseMessage = new ResponseMessage();
        $responseMessage->setMessageId($this->request->getMessageId());
        $responseMessage->setStatusCode($errorCode);
        $responseMessage->setErrorMessage($message);

        return $responseMessage;
    }
}