<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\Exception\MalformedNotificationException;
use Zbox\UnifiedPush\Exception\DomainException;

/**
 * Class NotificationBuilder
 * @package Zbox\UnifiedPush\Notification
 */
class NotificationBuilder
{
    /**
     * @var PayloadHandlerInterface[]
     */
    private $payloadHandlers;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var \ArrayIterator
     */
    private $notifications;

    /**
     * @param PayloadHandlerInterface $handler
     * @return $this
     */
    public function addPayloadHandler(PayloadHandlerInterface $handler)
    {
        $hash = spl_object_hash($handler);

        if (!isset($this->payloadHandlers[$hash])) {
            $this->payloadHandlers[$hash] = $handler;
        }

        return $this;
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
     * @param MessageInterface $message
     * @return $this
     */
    public function buildNotifications(MessageInterface $message)
    {
        $this->message  = $message;

        $recipientQueue = new \SplQueue();
        $recipientChunk = new \ArrayIterator();

        foreach ($message->getRecipientDeviceCollection() as $recipient) {
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
            $notification = $this->createNotification($recipientQueue->dequeue());
            $this->notifications->append($notification);
        }

        return $this;
    }

    /**
     * Returns created notification
     *
     * @param \ArrayIterator $recipients
     * @return Notification
     * @throws MalformedNotificationException
     */
    private function createNotification(\ArrayIterator $recipients)
    {
        $message = clone $this->message;
        $message->setRecipientDeviceCollection($recipients);

        foreach ($this->payloadHandlers as $handler) {
            if ($handler->isSupported($message)) {
                $packedPayload  = $this->handlePayload($handler, $message);
                $customData     = $handler->getCustomNotificationData();

                return
                    (new Notification())
                        ->setType($message->getMessageType())
                        ->setRecipients($recipients)
                        ->setPayload($packedPayload)
                        ->setCustomNotificationData($customData)
                    ;
            }
        }

        throw new DomainException(
            sprintf(
                'Unhandled message type %s',
                $message->getMessageType()
            )
        );
    }

    /**
     * @param PayloadHandlerInterface $handler
     * @param MessageInterface $message
     * @return string
     * @throws MalformedNotificationException
     */
    private function handlePayload(PayloadHandlerInterface $handler, MessageInterface $message)
    {
        $handler->setMessage($message);

        $payload = $handler->createPayload();
        $packedPayload = $handler->packPayload($payload);

        $this->validatePayload($handler, $packedPayload);

        return $packedPayload;
    }

    /**
     * Check if maximum size allowed for a notification payload exceeded
     *
     * @param PayloadHandlerInterface $handler
     * @param string $payload
     * @throws MalformedNotificationException
     */
    private function validatePayload(PayloadHandlerInterface $handler, $payload)
    {
        $message     = $this->message;
        $messageType = $message->getMessageType();

        $maxLength   = $handler->getPayloadMaxLength();

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
