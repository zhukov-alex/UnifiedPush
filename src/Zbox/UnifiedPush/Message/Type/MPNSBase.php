<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message\Type;

use Zbox\UnifiedPush\Message\MessageBase;
use Zbox\UnifiedPush\NotificationService\NotificationServices;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;
use Zbox\UnifiedPush\Exception\BadMethodCallException;

/**
 * Class MPNSBase
 * @package Zbox\UnifiedPush\Message\Type
 */
class MPNSBase extends MessageBase
{
    /**
     * MPNs does not support multicast sending
     */
    const MAX_RECIPIENTS_PER_MESSAGE_COUNT = 1;

    /**
     * Notification delivery interval
     *
     * @var int
     */
    protected $delayInterval;

    /**
     * @return string
     */
    public function getMessageType()
    {
        return NotificationServices::MICROSOFT_PUSH_NOTIFICATIONS_SERVICE;
    }

    /**
     * @return string
     */
    public function getMPNSType()
    {
        return static::MESSAGE_TYPE;
    }

    /**
     * No expiration time available in MPN
     *
     * @throws BadMethodCallException
     */
    public function getExpirationTime()
    {
        throw new BadMethodCallException("No expiration time available in MPN");
    }

    /**
     * @return int
     */
    public function getDelayInterval()
    {
        if (!$this->delayInterval) {
            $this->setDelayInterval(static::DELAY_INTERVAL_IMMEDIATE);
        }
        return $this->delayInterval;
    }

    /**
     * @param int $delayInterval
     * @return $this
     */
    public function setDelayInterval($delayInterval)
    {
        if (!in_array($delayInterval, array(
            static::DELAY_INTERVAL_IMMEDIATE,
            static::DELAY_INTERVAL_450,
            static::DELAY_INTERVAL_900
        ))) {
            throw new InvalidArgumentException('Delivery interval must be equal one of predefined interval flag');
        }
        $this->delayInterval = $delayInterval;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRecipient($token)
    {
        if (base64_encode(base64_decode($token)) !== $token) {
            throw new InvalidArgumentException(sprintf(
                'Device token must be base64 string. Token given: "%s"',
                $token
            ));
        }
        return true;
    }

    /**
     * @return \ReflectionProperty[]
     */
    public function getPropertiesList()
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

        return $properties;
    }
}
