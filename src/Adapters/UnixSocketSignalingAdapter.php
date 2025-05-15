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
use React\Socket\UnixServer;
use Webrtc\Signaling\SignalingInterface;
use React\Socket\Connector;

/**
 * UNIX Socket Signaling Adapter for WebRTC signaling implementation
 *
 * This class provides inter-process communication (IPC) via UNIX domain sockets
 * for WebRTC signaling. It's optimized for local communication between processes
 * on the same host with lower overhead than network sockets.
 *
 * Features:
 * - Creates UNIX domain socket server or client connections
 * - Uses filesystem-based socket paths for communication
 * - Provides efficient local process-to-process messaging
 * - Automatically cleans up a previous socket file on server start
 *
 * Usage:
 * - Server mode: Creates a socket file and listens for connections
 * - Client mode: Connects to an existing socket file
 *
 * Security Note:
 * - Socket files use filesystem permissions for access control
 * - Recommended for trusted environments (same host communication only)
 *
 * @package Webrtc\Signaling\Adapters
 */
class UnixSocketSignalingAdapter implements SignalingInterface
{
    private array $clients = [];
    private ?Closure $onReceiveCallback = null;

    public function __construct(
        private readonly string $address = '/tmp/signaling.sock',
        private readonly bool   $client = false
    ) {}

    /**
     * Start the UNIX socket signaling server or client.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->client) {
            $connector = new Connector();
            $connector->connect("unix://$this->address")->then(function ($conn) {
                $id = spl_object_hash($conn);
                $this->clients[$id] = $conn;

                $conn->on('data', function ($data) use ($id) {
                    if ($this->onReceiveCallback) {
                        ($this->onReceiveCallback)($id, $data);
                    }
                });
            });
        } else {
            @unlink($this->address);

            $server = new UnixServer($this->address);
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
     * Send a message to a connected UNIX socket client.
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
     * Register a callback to handle incoming UNIX socket messages.
     *
     * @param callable $callback
     * @return void
     */
    public function onOfferRequest(callable $callback): void
    {
        $this->onReceiveCallback = $callback;
    }
}
