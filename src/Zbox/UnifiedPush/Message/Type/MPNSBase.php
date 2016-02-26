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
     * The maximum size allowed for MPNS message payload is 3K bytes
     */
    const PAYLOAD_MAX_LENGTH = 3072;

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
     * @return \DOMDocument
     */
    public function createMessage()
    {
        $messageType = ucfirst(static::MESSAGE_TYPE);

        $message      = new \DOMDocument("1.0", "utf-8");
        $baseElement  = $message->createElement("wp:Notification");
        $baseElement->setAttribute("xmlns:wp", "WPNotification");
        $message->appendChild($baseElement);

        $rootElement = $message->createElement("wp:" . $messageType);
        $baseElement->appendChild($rootElement);

        foreach ($this->getPropertiesList() as $property)
        {
            $propertyName   = ucfirst($property->getName());
            $getterName     = 'get' . $propertyName;
            $value          = $this->$getterName();

            if ($value) {
                $name    = "wp:" . $propertyName;
                $element = $message->createElement($name, $value);
                $rootElement->appendChild($element);
            }
        }

        return $message->saveXML();
    }

    /**
     * @return \ReflectionProperty[]
     */
    protected function getPropertiesList()
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);

        return $properties;
    }

    /**
     * @param string $message
     * @param array $recipients
     * @return array
     */
    public function packMessage($message, $recipients)
    {
        $options = array(
            'X-MessageID'           => $this->getMessageIdentifier(),
            'X-NotificationClass'   => $this->getDelayInterval(),
            'X-WindowsPhone-Target' => static::MESSAGE_TYPE
        );

        return array(
            'body'        => $message,
            'recipients'  => $recipients,
            'options'     => $options
        );
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
     * @return string
     */
    public function __toString()
    {
        return $this->createMessage()->saveXML();
    }
}
