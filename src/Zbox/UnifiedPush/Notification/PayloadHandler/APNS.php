<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification\PayloadHandler;

use Zbox\UnifiedPush\Message\MessageInterface;
use Zbox\UnifiedPush\Message\Type\APNS as APNSMessage;
use Zbox\UnifiedPush\Notification\PayloadHandler;
use Zbox\UnifiedPush\Utils\JsonEncoder;

/**
 * Class APNS
 * @package Zbox\UnifiedPush\Notification\PayloadHandler
 */
class APNS extends PayloadHandler
{
    /**
     * The maximum size allowed for an iOS notification payload is 2 kilobytes
     * Prior to iOS 8 and in OS X, the maximum payload size is 256 bytes
     */
    const PAYLOAD_MAX_LENGTH = 2048;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function isSupported(MessageInterface $message)
    {
        return $message instanceof APNSMessage;
    }

    /**
     * @return array
     */
    public function createPayload()
    {
        /** @var APNSMessage $message */
        $message = $this->message;

        $payload = array(
            'aps' => array(
                'alert' => $message->getAlert(),
                'badge' => $message->getBadge(),
                'sound' => $message->getSound(),
                'category' => $message->getCategory(),
            )
        );

        $urlArgs = $message->getUrlArgs();
        if (!empty($urlArgs)) {
            $payload['aps']['url-args'] = $urlArgs;
        }

        if ($message->isContentAvailable() === true) {
            $payload['aps']['content-available'] = 1;
        }

        if ($message->isMutableContent() === true) {
            $payload['aps']['mutable-content'] = 1;
        }

        return array_merge($payload, $message->getCustomPayloadData());
    }

    /**
     * Pack message body into binary string
     *
     * @param array $payload
     * @return string
     */
    public function packPayload($payload)
    {
        $payload = JsonEncoder::jsonEncode($payload);

        /** @var APNSMessage $message */
        $message = $this->message;

        $recipientId = $message->getRecipientDeviceCollection()->current()->getIdentifier();

        $messageRecipientId = $this->notificationId . '_' . $recipientId;

        $packedPayload =
            pack('C', 1). // Command push
            pack('N', $messageRecipientId).
            pack('N', $message->getExpirationTime()->format('U')).
            pack('n', 32). // Token binary length
            pack('H*', $recipientId);
        pack('n', strlen($payload));

        $packedPayload .= $payload;

        return $packedPayload;
    }
}
