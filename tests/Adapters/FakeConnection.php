<?php

namespace Tests\Webrtc\Signaling\Adapters;

use Ratchet\ConnectionInterface;

class FakeConnection implements ConnectionInterface
{
    public int $resourceId;

    public function __construct(int $id)
    {
        $this->resourceId = $id;
    }

    public function send($data)
    {
    }

    public function close()
    {
    }
}
