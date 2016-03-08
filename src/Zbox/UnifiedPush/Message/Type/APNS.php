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
use Zbox\UnifiedPush\Utils\JsonEncoder;

/**
 * Class APNS
 * @package Zbox\UnifiedPush\Message\Type
 */
class APNS extends MessageBase
{
    /**
     * The maximum size allowed for an iOS notification payload is 2 kilobytes
     * Prior to iOS 8 and in OS X, the maximum payload size is 256 bytes
     */
    const PAYLOAD_MAX_LENGTH = 2048;

    /**
     * APNs does not support multicast sending
     */
    const MAX_RECIPIENTS_PER_MESSAGE_COUNT = 1;

    /**
     * The message’s priority. Provide one of the following values:
     * - 10 The push message is sent immediately
     * - 5 The push message is sent at a time that conserves power on the device receiving it
     *
     * @var integer
     */
    protected $priority;

    /**
     * Message text of an alert
     *
     * @var string
     */
    private $alert;

    /**
     * The number to display as the badge of the application icon
     *
     * @var integer
     */
    private $badge;

    /**
     * The name of a sound file in the application bundle
     *
     * @var string
     */
    private $sound;

    /**
     * Category option for custom notification actions (iOS 8+)
     *
     * @var string
     */
    private $category;

    /**
     * Provide this key with a value of 1 to indicate that new content is available
     *
     * @var bool
     */
    private $contentAvailable;

    /**
     * Custom properties
     *
     * @var array
     */
    private $customPayloadData = array();

    /**
     * Gets message type
     *
     * @return string
     */
    public function getMessageType()
    {
        return NotificationServices::APPLE_PUSH_NOTIFICATIONS_SERVICE;
    }

    /**
     * @return string
     */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
     * @param string $alert
     * @return $this
     */
    public function setAlert($alert)
    {
        if (!is_scalar($alert)) {
            $this->invalidArgumentException('Alert', 'a string');
        }
        $this->alert = $alert;

        return $this;
    }

    /**
     * @return int
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param int $badge
     * @return $this
     */
    public function setBadge($badge)
    {
        if (!is_int($badge)) {
            $this->invalidArgumentException('Badge', 'an integer');
        }

        $this->badge = $badge;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomPayloadData()
    {
        return $this->customPayloadData;
    }

    /**
     * @param array $customPayloadData
     */
    public function setCustomPayloadData($customPayloadData)
    {
        $this->customPayloadData = $customPayloadData;
    }

    /**
     * @return boolean
     */
    public function isContentAvailable()
    {
        return $this->contentAvailable;
    }

    /**
     * @param boolean $contentAvailable
     * @return $this
     */
    public function setContentAvailable($contentAvailable)
    {
        if (!is_bool($contentAvailable)) {
            $this->invalidArgumentException('ContentAvailable', 'a boolean');
        }

        $this->contentAvailable = $contentAvailable;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param string $sound
     * @return $this
     */
    public function setSound($sound)
    {
        if (!is_scalar($sound)) {
            $this->invalidArgumentException('Sound', 'an string');
        }
        $this->sound = $sound;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        if (!is_scalar($category)) {
            $this->invalidArgumentException('Category', 'an string');
        }
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function createPayload()
    {
        $payload = array(
            'aps' => array(
                'alert' => $this->getAlert(),
                'badge' => $this->getBadge(),
                'sound' => $this->getSound(),
                'category' => $this->getCategory(),
            )
        );

        if ($this->isContentAvailable() === true) {
            $payload['aps']['content-available'] = 1;
        }

        return array_merge($payload, $this->getCustomPayloadData());
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

        $recipientId = $this->getRecipientDevice()->getIdentifier();

        $messageRecipientId = $this->getMessageIdentifier() . '_' . $recipientId;

        $packedPayload =
            pack('C', 1). // Command push
            pack('N', $messageRecipientId).
            pack('N', $this->getExpirationTime()->format('U')).
            pack('n', 32). // Token binary length
            pack('H*', $recipientId);
        pack('n', strlen($payload));

        $packedPayload .= $payload;

        return $packedPayload;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRecipient($token)
    {
        if (!ctype_xdigit($token)) {
            throw new InvalidArgumentException(sprintf(
                'Device token must be a hexadecimal digit. Token given: "%s"',
                $token
            ));
        }

        if (strlen($token) != 64) {
            throw new InvalidArgumentException(sprintf(
                'Device token must be a 64 charsets, Token length given: %d.',
                mb_strlen($token)
            ));
        }
        return true;
    }
}
