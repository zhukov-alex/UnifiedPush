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
    private $notificationCollection;

    /**
     * NotificationBuilder constructor.
     */
    public function __construct()
    {
        $this->notificationCollection = new \ArrayIterator();
    }

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
     * @return \ArrayIterator
     */
    public function getNotificationCollection()
    {
        return $this->notificationCollection;
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
            $this->notificationCollection->append($notification);
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
