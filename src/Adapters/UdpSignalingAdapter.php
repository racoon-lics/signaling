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
use React\Datagram\Factory as DatagramFactory;
use Webrtc\Signaling\SignalingInterface;
use React\Datagram\Socket;

/**
 * UDP Signaling Adapter for WebRTC signaling implementation
 *
 * This class provides UDP-based signaling functionality for WebRTC applications.
 * It can operate in either server or client mode, handling message exchange
 * over UDP sockets with lower overhead but less reliability than TCP.
 *
 * Features:
 * - Creates UDP server or client connections
 * - Handles datagram message sending and receiving
 * - Uses ReactPHP's datagram component for async operations
 * - Supports both unicast and broadcast messaging patterns
 *
 * Usage:
 * - Server mode: Listens for incoming datagrams on specified port
 * - Client mode: Sends messages to server and handles responses
 *
 * Note: UDP is connectionless, so clientId represents remote address:port pairs
 *
 * @package Webrtc\Signaling\Adapters
 */
class UdpSignalingAdapter implements SignalingInterface
{
    private ?Socket $socket = null;
    private ?Closure $onReceiveCallback = null;

    public function __construct(
        private readonly string $address = '0.0.0.0',
        private readonly int    $port = 5000,
        private readonly bool   $client = false
    ) {}

    /**
     * Start the UDP signaling server or client.
     *
     * @return void
     */
    public function start(): void
    {
        $factory = new DatagramFactory();

        if ($this->client) {
            $factory->createClient("$this->address:$this->port")->then(function (Socket $client) {
                $this->socket = $client;

                $client->on('message', function ($message, $serverAddress) {
                    if ($this->onReceiveCallback) {
                        ($this->onReceiveCallback)($serverAddress, $message);
                    }
                });
            });
        } else {
            $factory->createServer("$this->address:$this->port")->then(function (Socket $server) {
                $this->socket = $server;

                $server->on('message', function ($message, $address) {
                    if ($this->onReceiveCallback) {
                        ($this->onReceiveCallback)($address, $message);
                    }
                });
            });
        }
    }

    /**
     * Send a message to a specific UDP address.
     *
     * @param string $clientId The address to send the message to (e.g., "127.0.0.1:12345")
     * @param mixed $message
     * @return void
     */
    public function send(string $clientId, mixed $message): void
    {
        $this->socket?->send($message, $clientId);
    }

    /**
     * Register a callback to handle incoming UDP messages.
     *
     * @param callable $callback
     * @return void
     */
    public function onOfferRequest(callable $callback): void
    {
        $this->onReceiveCallback = $callback;
    }
}

