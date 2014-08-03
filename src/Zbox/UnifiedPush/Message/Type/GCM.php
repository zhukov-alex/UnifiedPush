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

/**
 * Class GCM
 * @package Zbox\UnifiedPush\Message\Type
 */
class GCM extends MessageBase
{
    /**
     * The maximum size allowed for an Android notification payload is 4096 bytes
     */
    const PAYLOAD_MAX_LENGTH = 4096;

    /**
     * It`s possible to send the same message to up to 1000 registration IDs in one request
     */
    const MAX_RECIPIENTS_PER_MESSAGE_COUNT = 1000;

    /**
     * Allow test the request without actually sending the message
     *
     * @var boolean
     */
    private $dryRun = false;

    /**
     * If there is already a message with the same collapse key (and registration ID)
     * stored and waiting for delivery, the old message will be discarded
     * and the new message will take its place
     *
     * @var string
     */
    private $collapseKey;

    /**
     * Message payload data
     *
     * @var array
     */
    private $payloadData;

    /**
     * If the device is connected but idle, the message will still be delivered right away
     * unless the delay_while_idle flag is set to true
     *
     * @var boolean
     */
    private $delayWhileIdle;

    /**
     * Package name of you application
     *
     * @var string
     */
    private $packageName;

    /**
     * @return string
     */
    public function getMessageType()
    {
        return NotificationServices::GOOGLE_CLOUD_MESSAGING;
    }

    /**
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * @param string $collapseKey
     * @return $this
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDelayWhileIdle()
    {
        return $this->delayWhileIdle;
    }

    /**
     * @param boolean $delayWhileIdle
     * @return $this
     */
    public function setDelayWhileIdle($delayWhileIdle)
    {
        $this->delayWhileIdle = $delayWhileIdle;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * @param boolean $dryRun
     * @return $this
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @param string $packageName
     * @return $this
     */
    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayloadData()
    {
        return $this->payloadData;
    }

    /**
     * @param array $payloadData
     * @return $this
     */
    public function setPayloadData($payloadData)
    {
        $this->payloadData = $payloadData;
        return $this;
    }

    /**
     * @param array $registrationIds
     * @return array
     */
    public function createMessage($registrationIds)
    {
        $ttl = $this->getExpirationTime()->format('U') - time();

        $message = array(
            'collapse_key'      => $this->getCollapseKey(),
            'delay_while_idle'  => $this->isDelayWhileIdle(),
            'registration_ids'  => $registrationIds,
            'data'              => $this->getPayloadData(),
            'time_to_live'      => $ttl
        );

        return $message;
    }

    /**
     * No packing is required
     *
     * @param string $message
     * @return string
     */
    public function packMessage($message)
    {
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRecipient($token)
    {
        if (!preg_match('#^[0-9a-z\-\_]+$#i', $token)) {
            throw new InvalidArgumentException(sprintf(
                'Device token must be mask "%s". Token given: "%s"',
                '^[0-9a-z\-\_]+$#i',
                $token
            ));
        }
        return true;
    }
}
