<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Exception\MalformedNotificationException;

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
     * @return Notification|null
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
        $recipientChunk = new \ArrayIterator();

        while ($recipient = $message->getRecipientDevice()) {
            $recipientChunk->append($recipient);

            if ($recipientChunk->count() >= $message->getMaxRecipientsPerMessage()) {
                $recipientQueue->enqueue($recipientChunk);
                $recipientChunk = new \ArrayIterator();
            }
        }

        if ($recipientChunk->count()) {
            $recipientQueue->enqueue($recipientChunk);
        }

        while (!$recipientQueue->isEmpty()) {
            $message->setRecipientCollection($recipientQueue->dequeue());
            $notification = $this->buildNotification();
            $this->notifications->append($notification);
        }

        return $this;
    }

    /**
     * Returns created notification
     *
     * @return Notification
     */
    private function buildNotification()
    {
        $message         = $this->message;
        $payload         = $message->createPayload();
        $recipients      = $message->getRecipientCollection();

        $packedPayload = $message->packPayload($payload);
        $this->validatePayload($packedPayload);

        return
            (new Notification())
                ->setPayload($packedPayload)
                ->setRecipients($recipients)
                ->setMessage($message)
            ;
    }

    /**
     * Check if maximum size allowed for a notification payload exceeded
     *
     * @param string $payload
     * @throws MalformedNotificationException
     */
    protected function validatePayload($payload)
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
    }
}
