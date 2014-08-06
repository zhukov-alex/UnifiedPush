<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Exception;

class InvalidRecipientException extends DispatchMessageException
{
    /**
     * Collection of invalid recipient devices
     *
     * @var \ArrayIterator
     */
    private $recipientCollection;

    /**
     * @param string $message
     * @param array $recipients
     */
    public function __construct($message, array $recipients)
    {
        $this->recipientCollection = new \ArrayIterator($recipients);

        parent::__construct($message);
    }

    /**
     * @return RecipientDevice
     */
    public function getRecipient()
    {
        $collection = $this->recipientCollection;

        if ($collection->valid()) {
            $device = $collection->current();
            $collection->next();
            return $device;
        }
        return null;
    }
}
