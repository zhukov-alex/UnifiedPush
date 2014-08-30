<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Exception\DomainException;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class RecipientDevice
 * @package Zbox\UnifiedPush\Message
 */
class RecipientDevice
{
    /**
     * Device identifier statuses
     */
    const DEVICE_ACTUAL                  = 1;
    const DEVICE_NOT_READY               = 2;
    const DEVICE_NOT_REGISTERED          = 3;
    const IDENTIFIER_NEED_TO_BE_REPLACED = 4;

    /**
     * Recipient device identifier
     *
     * @var string
     */
    private $identifier;

    /**
     * Current status
     *
     * @var int
     */
    private $identifierStatus;

    /**
     *  Sender should replace the ID on future requests
     * @var string
     */
    private $idToReplaceTo;

    /**
     * @param string $deviceIdentifier
     * @param MessageInterface $message
     * @return $this
     */
    public function __construct($deviceIdentifier, MessageInterface $message)
    {
        $this->setIdentifier($deviceIdentifier, $message);
        $this->setIdentifierStatus(self::DEVICE_ACTUAL);

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @param MessageInterface $message
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setIdentifier($identifier, MessageInterface $message)
    {
        if (!$message->validateRecipient($identifier)) {
            throw new InvalidArgumentException("Device identifier is not valid");
        }
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return int
     */
    public function getIdentifierStatus()
    {
        return $this->identifierStatus;
    }

    /**
     * @param int $identifierStatus
     * @return $this
     */
    public function setIdentifierStatus($identifierStatus)
    {
        if (!in_array($identifierStatus, array(
            self::DEVICE_ACTUAL,
            self::DEVICE_NOT_READY,
            self::DEVICE_NOT_REGISTERED,
            self::IDENTIFIER_NEED_TO_BE_REPLACED
        ))) {
            throw new DomainException("Unknown status of device identifier");
        }

        $this->identifierStatus = $identifierStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdToReplaceTo()
    {
        return $this->idToReplaceTo;
    }

    /**
     * @param string $idToReplaceTo
     * @return $this
     */
    public function setIdToReplaceTo($idToReplaceTo)
    {
        $this->setIdentifierStatus(self::IDENTIFIER_NEED_TO_BE_REPLACED);
        $this->idToReplaceTo = $idToReplaceTo;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getIdentifier();
    }
}