<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Utils\JsonEncoder;

/**
 * Class NotificationBuilder
 * @package Zbox\UnifiedPush\Message
 */
class NotificationBuilder
{
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var \ArrayIterator
     */
    private $notifications;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->notifications = new \ArrayIterator();
        $this->message = $message;
        $this->buildNotifications();
    }

    /**
     * @return string
     */
    public function getNotification()
    {
        $collection = $this->notifications;

        if ($collection->valid()) {
            $notification = $collection->current();
            $collection->next();
            return $notification;
        }
        return null;
    }

    /**
     * @return $this
     */
    public function buildNotifications()
    {
        $message     = $this->message;
        $recipients  = $message->getRecipientCollection()->getArrayCopy();

        $chunks = array_chunk($recipients, $message->getMaxRecipientsPerMessage());

        foreach ($chunks as $chunk) {
            $notification = $this->buildNotification($chunk);
            $this->notifications->append($notification);
        }

        return $this;
    }

    /**
     * Returns validated and encoded message
     *
     * @param array $recipientIds
     * @return string a binary string containing data
     */
    private function buildNotification($recipientIds)
    {
        $message         = $this->message;
        $messageData     = $message->createMessage($recipientIds);
        $encodedMessage  = JsonEncoder::jsonEncode($messageData);

        $this->validatePayload($encodedMessage);

        $notification = $message->packMessage($encodedMessage, $recipientIds);

        return $notification;
    }

    /**
     * Check if maximum size allowed for a notification payload exceeded
     *
     * @param string $payload
     * @throws MalformedNotificationException
     * @return $this
     */
    public function validatePayload($payload)
    {
        $message     = $this->message;
        $maxLength   = $message->getPayloadMaxLength();
        $messageType = $message->getMessageType();

        if (strlen($payload) > $maxLength) {
            throw new MalformedNotificationException(
                sprintf(
                    "The maximum size allowed for '%s' notification payload is %d bytes",
                    $messageType,
                    $maxLength
                )
            );
        }
        return $this;
    }
}