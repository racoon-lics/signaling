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

interface SignalingInterface {
    public function start(): void;
    public function send(string $clientId, mixed $message): void;
    public function onOfferRequest(callable $callback): void;
}
