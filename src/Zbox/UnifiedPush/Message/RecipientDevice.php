<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message;

use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class RecipientDevice
 * @package Zbox\UnifiedPush\Message
 */
class RecipientDevice
{
    /**
     * Recipient device identifier
     *
     * @var string
     */
    private $identifier;

    /**
     * @param string $deviceIdentifier
     * @param MessageInterface $message
     */
    public function __construct($deviceIdentifier, MessageInterface $message)
    {
        $this->setIdentifier($deviceIdentifier, $message);
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
}