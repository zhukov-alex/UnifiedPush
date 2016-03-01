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
     * Generates number of notifications by message recipient count
     * and notification service limitations
     *
     * @return $this
     */
    public function buildNotifications()
    {
        $message        = $this->message;
        $recipientQueue = new \SplQueue();
        $recipientChunk = array();

        while ($recipient = $message->getRecipient()) {
            $recipientChunk[] = $recipient;

            if (count($recipientChunk) >= $message->getMaxRecipientsPerMessage()) {
                $recipientQueue->enqueue($recipientChunk);
                $recipientChunk = [];
            }
        }

        if (!empty($recipientChunk)) {
            $recipientQueue->enqueue($recipientChunk);
        }

        while (!$recipientQueue->isEmpty()) {
            $notification = $this->buildNotification($recipientQueue->dequeue());
            $this->notifications->append($notification);
        }

        return $this;
    }

    /**
     * Returns validated and encoded message
     *
     * @param array $recipients
     * @return array
     */
    private function buildNotification($recipients)
    {
        $message         = $this->message;
        $messageData     = $message->createMessage($recipients);

        if (is_array($messageData)) {
            $messageData = JsonEncoder::jsonEncode($messageData);
        }

        $this->validatePayload($messageData);

        $notification = $message->packMessage($messageData, $recipients);

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
