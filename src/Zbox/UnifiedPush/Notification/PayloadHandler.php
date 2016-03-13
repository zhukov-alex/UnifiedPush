<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class PayloadHandler
 * @package Zbox\UnifiedPush\Notification
 */
abstract class PayloadHandler implements PayloadHandlerInterface
{
    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    abstract public function isSupported(MessageInterface $message);

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        if (!$this->isSupported($message)) {
            throw new InvalidArgumentException('Message type is not supported');
        }

        $this->message = $message;

        return $this;
    }

    /**
     * Gets maximum size allowed for notification payload
     *
     * @return int
     */
    public function getPayloadMaxLength()
    {
        return static::PAYLOAD_MAX_LENGTH;
    }

    /**
     * @return array
     */
    public function getCustomNotificationData()
    {
        return array();
    }
}
