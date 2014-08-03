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
    protected $expirationTime;

    /**
     * @return string
     */
    public function getMessageType()
    {
        return NotificationServices::MICROSOFT_PUSH_NOTIFICATIONS_SERVICE;
    }

    /**
     * @return int
     */
    public function getExpirationTime()
    {
        if (!$this->expirationTime) {
            $this->setExpirationTime(static::DELAY_INTERVAL_IMMEDIATE);
        }
        return $this->expirationTime;
    }

    /**
     * @param int $expirationTime
     * @return $this
     */
    public function setExpirationTime($expirationTime)
    {
        if (!in_array($expirationTime, array(
            static::DELAY_INTERVAL_IMMEDIATE,
            static::DELAY_INTERVAL_450,
            static::DELAY_INTERVAL_900
        ))) {
            throw new InvalidArgumentException('Delivery interval must be equal one of predefined interval flag');
        }
        $this->expirationTime = $expirationTime;

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
        $message->appendChild($element);

        $rootElement = $message->createElement("wp:" . $messageType);
        $baseElement->appendChild($rootElement);

        foreach ($this->getPropertiesList() as $property)
        {
            if ($property->getValue()) {
                $name    = "wp:" . ucfirst($property->getName());
                $value   = $property->getValue();

                $element = $message->createElement($name, $value);
                $rootElement->appendChild($element);
            }
        }

        return $message;
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
     * @return array
     */
    public function packMessage($message, $recipientIds)
    {
        $options = array(
            'X-MessageID'           => $this->getMessageIdentifier(),
            'X-NotificationClass'   => $this->getExpirationTime(),
            'X-WindowsPhone-Target' => static::MESSAGE_TYPE
        );

        return array(
            'body'      => $message,
            'options'   => $options,
            'recipient' => $recipientIds[0]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateRecipient($token)
    {
        if (base64_encode(base64_decode($token)) === $token) {
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
