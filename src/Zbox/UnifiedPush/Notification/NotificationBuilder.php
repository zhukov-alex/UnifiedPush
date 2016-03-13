<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\NotificationService\NotificationServices;
use Zbox\UnifiedPush\Exception\MalformedNotificationException;

/**
 * Class NotificationBuilder
 * @package Zbox\UnifiedPush\Notification
 */
class NotificationBuilder
{
    /**
     * @var
     */
    private $payloadHandler;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var \ArrayIterator
     */
    private $notifications;

    /**
     * @param string $type
     * @return PayloadHandlerInterface
     */
    public function getHandlerByService($type)
    {
        NotificationServices::validateServiceName($type);

        if (empty($this->payloadHandler[$type])) {
            $handlerClass = sprintf('\Zbox\UnifiedPush\Notification\PayloadHandler\%s', $type);
            $this->payloadHandler[$type] = new $handlerClass();
        }

        return $this->payloadHandler[$type];
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
        $message        = clone $this->message;
        $message->setRecipientDeviceCollection($recipients);

        $payloadHandler = $this->getHandlerByService($message->getMessageType());
        $payloadHandler->setMessage($message);

        $payload        = $payloadHandler->createPayload();
        $packedPayload  = $payloadHandler->packPayload($payload);
        $customData     = $payloadHandler->getCustomNotificationData();

        $this->validatePayload($packedPayload);

        return
            (new Notification())
                ->setRecipients($recipients)
                ->setPayload($packedPayload)
                ->setCustomNotificationData($customData)
            ;
    }

    /**
     * Check if maximum size allowed for a notification payload exceeded
     *
     * @param string $payload
     * @throws MalformedNotificationException
     */
    private function validatePayload($payload)
    {
        $message     = $this->message;
        $messageType = $message->getMessageType();

        $payloadHandler = $this->getHandlerByService($messageType);
        $maxLength      = $payloadHandler->getPayloadMaxLength();

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
