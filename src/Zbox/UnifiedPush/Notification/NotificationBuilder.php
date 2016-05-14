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
     * @return PayloadHandlerInterface[]
     */
    public function getPayloadHandlers()
    {
        return $this->payloadHandlers;
    }

    /**
     * Generates number of notifications by message recipient count
     * and notification service limitations
     *
     * @param MessageInterface $message
     * @return \ArrayIterator
     */
    public function buildNotifications(MessageInterface $message)
    {
        $notifications  = new \ArrayIterator();
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
            $notification = $this->createNotification($recipientQueue->dequeue(), $message);
            $notifications->append($notification);
        }

        return $notifications;
    }

    /**
     * Returns created notification
     *
     * @param \ArrayIterator $recipients
     * @param MessageInterface $message
     * @return Notification
     */
    private function createNotification(\ArrayIterator $recipients, MessageInterface $message)
    {
        $message = clone $message;
        $message->setRecipientDeviceCollection($recipients);

        $handlers = $this->getPayloadHandlers();

        foreach ($handlers as $handler) {
            if ($handler->isSupported($message)) {
                $notificationId = uniqid();
                $handler
                    ->setNotificationId($notificationId)
                    ->setMessage($message);

                $packedPayload  = $this->handlePayload($handler);
                $customData     = $handler->getCustomNotificationData();

                $notification = new Notification();
                $notification
                    ->setIdentifier($notificationId)
                    ->setType($message->getMessageType())
                    ->setRecipients($recipients)
                    ->setPayload($packedPayload)
                    ->setCustomNotificationData($customData)
                ;
                return $notification;
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
     * @return string
     * @throws MalformedNotificationException
     */
    private function handlePayload(PayloadHandlerInterface $handler)
    {
        $payload        = $handler->createPayload();
        $packedPayload  = $handler->packPayload($payload);

        $handler->validatePayload($packedPayload);

        return $packedPayload;
    }
}
