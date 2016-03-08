<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

/**
 * Class Notification
 * @package Zbox\UnifiedPush\Message
 */
class Notification
{
    /**
     * @var string
     */
    protected $payload;

    /**
     * @var \ArrayIterator
     */
    protected $recipients;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param \ArrayIterator $recipients
     * @return $this
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
        return $this;
    }
}
