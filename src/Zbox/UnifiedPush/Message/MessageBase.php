<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Exception\BadMethodCallException,
    Zbox\UnifiedPush\Exception\InvalidArgumentException,
    Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Utils\DateTimeHelper;

/**
 * Class MessageBase
 * @package Zbox\UnifiedPush\Message
 */
abstract class MessageBase implements MessageInterface
{
    /**
     * Default modifier (a date/time string)
     */
    const DEFAULT_EXPIRATION_TIME_MODIFIER = '4 weeks';

    /**
     * A UNIX epoch date expressed in seconds (UTC) that identifies
     * when the notification is no longer valid and can be discarded
     *
     * @var \DateTime
     */
    protected $expirationTime;

    /**
     * An arbitrary, opaque value that identifies this notification.
     * This identifier is used for reporting errors to your server
     *
     * @var string
     */
    protected $messageIdentifier;


    /**
     * Collection of recipient devices
     *
     * @var \ArrayIterator
     */
    protected $recipientCollection;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->setMessageIdentifier(uniqid());
        $this->recipientCollection = new \ArrayIterator();

        foreach ($data as $key => $value) {
            if (!isset($this->$key)) {
                $this->badMethodCallException($key);
            }

            $this->{'set'.ucfirst($key)}($value);
        }

        return $this;
    }

    /**
     * Checks if recipient`s token is valid
     *
     * @param string $token
     * @return bool
     * @throws InvalidArgumentException
     */
    abstract public function validateRecipient($token);

    /**
     * Gets number of recipients allowed for single notification
     *
     * @return int
     */
    public function getMaxRecipientsPerMessage()
    {
        return static::MAX_RECIPIENTS_PER_MESSAGE_COUNT;
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
     * @return RecipientDevice
     */
    public function getRecipient()
    {
        $collection = $this->recipientCollection;

        if ($collection->valid()) {
            $device = $collection->current();
            $collection->next();
            return $device;
        }
        return null;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRecipientCollection()
    {
        return $this->recipientCollection;
    }

    /**
     * @param \ArrayIterator $collection
     * @return $this
     */
    public function setRecipientCollection(\ArrayIterator $collection)
    {
        while ($collection->valid()) {
            $deviceIdentifier = $collection->current();
            $this->addRecipient($deviceIdentifier);
            $collection->next();
        }

        return $this;
    }

    /**
     * @param string $deviceIdentifier
     * @return $this
     */
    public function addRecipient($deviceIdentifier)
    {
        $device = new RecipientDevice($deviceIdentifier, $this);
        $this->recipientCollection->append($device);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationTime()
    {
        if (!$this->expirationTime) {
            $this->setExpirationTime(new \DateTime(self::DEFAULT_EXPIRATION_TIME_MODIFIER));
        }
        return $this->expirationTime;
    }

    /**
     * @param \DateTime $expirationTime
     * @return $this
     */
    public function setExpirationTime(\DateTime $expirationTime)
    {
        $this->expirationTime = DateTimeHelper::updateTimezoneToUniversal($expirationTime);
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageIdentifier()
    {
        return $this->messageIdentifier;
    }

    /**
     * @param string $messageIdentifier
     * @throws InvalidArgumentException
     */
    public function setMessageIdentifier($messageIdentifier)
    {
        if (!is_scalar($messageIdentifier)) {
            throw new InvalidArgumentException("Message identifier must be a scalar value");
        }
        $this->messageIdentifier = $messageIdentifier;
    }

    /**
     * Bad method call exception
     *
     * @param string $name
     * @throws BadMethodCallException
     */
    protected function badMethodCallException($name)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' of notification type '%s'.", $name, get_class($this))
        );
    }

    /**
     * Invalid argument exception
     *
     * @param string $parameterName
     * @param string $expectedType
     */
    protected function invalidArgumentException($parameterName, $expectedType)
    {
        throw new InvalidArgumentException(
            sprintf(
                "Value type of '%s'::'%s' parameter is '%s' of notification type '%s'.",
                get_class($this),
                $parameterName,
                $expectedType
            )
        );
    }

    /**
     * Error handler for unknown property notification
     *
     * @param string $name
     */
    public function __get($name)
    {
        $this->badMethodCallException($name);
    }

    /**
     * Error handler for unknown property of notification
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->badMethodCallException($name);
    }
}
