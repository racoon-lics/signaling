<?php

/**
 * This file is part of the PHP WebRTC package.
 *
 * (c) Amin Yazdanpanah <https://www.aminyazdanpanah.com/#contact>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webrtc\Signaling\Adapters;

use Closure;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Webrtc\Signaling\SignalingInterface;

/**
 * WebSocket Signaling Adapter for WebRTC signaling implementation
 *
 * This class provides WebSocket-based signaling functionality for WebRTC applications,
 * enabling full-duplex communication between clients and server over a single TCP connection.
 * It implements both SignalingInterface and Ratchet's MessageComponentInterface.
 *
 * Features:
 * - Full-duplex real-time communication using WebSocket protocol
 * - Built on Ratchet PHP WebSocket library
 * - Handles connection lifecycle (open/message/close/error)
 * - Supports multiple concurrent client connections
 * - Uses an HTTP upgrade mechanism for WebSocket handshake
 *
 * Usage:
 * - Creates a WebSocket server listening on specified port
 * - Manages client connections and message routing
 * - Integrates with WebRTC signaling workflow
 *
 * Security Considerations:
 * - Should be used behind a secure proxy (wss://) in production
 * - Implements basic connection error handling
 *
 * @package Webrtc\Signaling\Adapters
 */
class WebSocketSignalingAdapter implements SignalingInterface, MessageComponentInterface
{
    private array $clients = [];
    private ?Closure $onReceiveCallback = null;

    public function __construct(
        private readonly int    $port = 5000,
        private readonly string $address = '0.0.0.0')
    {
    }

    /**
     * Start the WebSocket signaling server.
     *
     * @return void
     */
    public function start(): void
    {
        $server = IoServer::factory(new HttpServer(new WsServer($this)), $this->port, $this->address);
        $server->run();
    }

    /**
     * Send a message to a specific client by ID.
     *
     * @param string $clientId
     * @param mixed $message
     * @return void
     */
    public function send(string $clientId, mixed $message): void
    {
        if (isset($this->clients[$clientId])) {
            $this->clients[$clientId]->send($message);
        }
    }

    /**
     * Set the callback to handle incoming offer requests.
     *
     * @param callable $callback
     * @return void
     */
    public function onOfferRequest(callable $callback): void
    {
        $this->onReceiveCallback = $callback;
    }

    /**
     * Handle a new client connection.
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients[$conn->resourceId] = $conn;
    }

    /**
     * Handle an incoming message from a client.
     *
     * @param ConnectionInterface $from
     * @param string $msg
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        if ($this->onReceiveCallback) {
            ($this->onReceiveCallback)($from->resourceId, $msg);
        }
    }

    /**
     * Handle a client disconnection.
     *
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        unset($this->clients[$conn->resourceId]);
    }

    /**
     * Handle an error on the connection.
     *
     * @param ConnectionInterface $conn
     * @param Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        $conn->close();
    }
}
