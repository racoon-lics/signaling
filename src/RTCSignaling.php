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

use Closure;
use Exception;
use Webrtcsignaler\WebSocketMessage;

/**
 * WebRTC Signaling Facade
 *
 * This class serves as a unified interface for WebRTC signaling operations,
 * delegating to a concrete signaling adapter while providing additional
 * message processing and request handling capabilities.
 *
 * Features:
 * - Acts as a decorator for various signaling adapters (WebSocket, TCP, etc.)
 * - Automatically parses incoming WebSocket messages into Request objects
 * - Provides a consistent interface regardless of underlying transport
 * - Handles message serialization/deserialization transparently
 *
 * Usage:
 * - Wraps any SignalingInterface implementation
 * - Processes raw messages into structured Request objects
 * - Maintains consistent behavior across different transport protocols
 *
 * Architecture:
 * - Implements the adapter pattern to abstract signaling transport details
 * - Uses protocol buffers for message serialization (WebSocketMessage)
 * - Provides automatic callback decoration for message processing
 *
 * @package Webrtc\Signaling
 */
class RTCSignaling implements SignalingInterface
{
    private SignalingInterface $adapter;

    public function __construct(SignalingInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Start the signaling server.
     *
     * @return void
     */
    public function start(): void
    {
        $this->adapter->start();
    }

    /**
     * Send a message to a specific client.
     *
     * @param string $clientId
     * @param mixed $message
     * @return void
     */
    public function send(string $clientId, mixed $message): void
    {
        $this->adapter->send($clientId, $message);
    }

    /**
     * Register a callback to handle incoming offer requests.
     *
     * @param callable $callback
     * @return void
     */
    public function onOfferRequest(callable $callback): void
    {
        $this->adapter->onOfferRequest($this->decorateClosure($callback));
    }

    /**
     * Decorate the callback to automatically parse WebSocket messages into Request objects.
     *
     * @param callable $func
     * @return Closure
     */
    private function decorateClosure(callable $func): Closure
    {
        return function ($client, $message) use ($func) {
            $request = $this->createRequestObject($client, $message);
            if ($request !== null) {
                call_user_func($func, $request);
            }
        };
    }

    /**
     * Create a Request object from a WebSocket binary message.
     *
     * @param mixed $client
     * @param string $message
     * @return Request|null
     * @throws Exception
     */
    private function createRequestObject(mixed $client, string $message): ?Request
    {
        $wsMessage = new WebSocketMessage();
        $wsMessage->mergeFromString($message);
        if ($wsMessage->hasRequest()) {
            return new Request($this->adapter, $wsMessage, $client);
        }

        return null;
    }
}
