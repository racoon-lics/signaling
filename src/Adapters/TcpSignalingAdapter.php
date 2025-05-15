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
use React\Socket\SocketServer;
use Webrtc\Signaling\SignalingInterface;
use React\Socket\Connector;

/**
 * TCP Signaling Adapter for WebRTC signaling implementation
 *
 * This class provides TCP-based signaling functionality for WebRTC applications.
 * It can operate in either server or client mode, handling peer connections
 * and message exchange over TCP sockets.
 *
 * Features:
 * - Creates TCP server or client connections
 * - Manages multiple client connections
 * - Handles message sending and receiving
 * - Tracks connection state
 *
 * Usage:
 * - Server mode: Listens for incoming connections and relays messages
 * - Client mode: Connects to a signaling server and handles messages
 *
 * @package Webrtc\Signaling\Adapters
 */
class TcpSignalingAdapter implements SignalingInterface
{
    private array $clients = [];
    private ?Closure $onReceiveCallback = null;

    public function __construct(
        private readonly string $address = '0.0.0.0',
        private readonly int    $port = 5000,
        private readonly bool $client = false
    ) {}

    /**
     * Start the TCP signaling server or client.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->client) {
            $connector = new Connector();
            $connector->connect("tcp://$this->address:$this->port")->then(function ($conn) {
                $id = spl_object_hash($conn);
                $this->clients[$id] = $conn;

                $conn->on('data', function ($data) use ($id) {
                    if ($this->onReceiveCallback) {
                        ($this->onReceiveCallback)($id, $data);
                    }
                });
            });
        } else {
            $server = new SocketServer("$this->address:$this->port");
            $server->on('connection', function ($conn) {
                $id = spl_object_hash($conn);
                $this->clients[$id] = $conn;

                $conn->on('data', function ($data) use ($id) {
                    if ($this->onReceiveCallback) {
                        ($this->onReceiveCallback)($id, $data);
                    }
                });

                $conn->on('close', function () use ($id) {
                    unset($this->clients[$id]);
                });
            });
        }
    }

    /**
     * Send a message to a connected TCP client.
     *
     * @param string $clientId
     * @param mixed $message
     * @return void
     */
    public function send(string $clientId, mixed $message): void
    {
        if (isset($this->clients[$clientId])) {
            $this->clients[$clientId]->write($message);
        }
    }

    /**
     * Register a callback to handle incoming data from clients.
     *
     * @param callable $callback
     * @return void
     */
    public function onOfferRequest(callable $callback): void
    {
        $this->onReceiveCallback = $callback;
    }
}

