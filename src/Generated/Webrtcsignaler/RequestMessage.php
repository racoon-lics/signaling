<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: src/Signaling/proto/webrtcsignaler.proto

namespace Webrtcsignaler;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>webrtcsignaler.RequestMessage</code>
 */
class RequestMessage extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string messageId = 1;</code>
     */
    protected $messageId = '';
    /**
     * Generated from protobuf field <code>string id = 2;</code>
     */
    protected $id = '';
    /**
     * Generated from protobuf field <code>.webrtcsignaler.SignalMessage payload = 3;</code>
     */
    protected $payload = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $messageId
     *     @type string $id
     *     @type \Webrtcsignaler\SignalMessage $payload
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Src\Signaling\Proto\Webrtcsignaler::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string messageId = 1;</code>
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * Generated from protobuf field <code>string messageId = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setMessageId($var)
    {
        GPBUtil::checkString($var, True);
        $this->messageId = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string id = 2;</code>
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generated from protobuf field <code>string id = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkString($var, True);
        $this->id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.webrtcsignaler.SignalMessage payload = 3;</code>
     * @return \Webrtcsignaler\SignalMessage|null
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function hasPayload()
    {
        return isset($this->payload);
    }

    public function clearPayload()
    {
        unset($this->payload);
    }

    /**
     * Generated from protobuf field <code>.webrtcsignaler.SignalMessage payload = 3;</code>
     * @param \Webrtcsignaler\SignalMessage $var
     * @return $this
     */
    public function setPayload($var)
    {
        GPBUtil::checkMessage($var, \Webrtcsignaler\SignalMessage::class);
        $this->payload = $var;

        return $this;
    }

}

