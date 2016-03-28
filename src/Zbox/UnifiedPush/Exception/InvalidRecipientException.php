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
     * @param \ArrayIterator $recipients
     */
    public function __construct($message, \ArrayIterator $recipients)
    {
        $this->recipientCollection = $recipients;

        parent::__construct($message);
    }

    /**
     * @return \ArrayIterator
     */
    public function getRecipientCollection()
    {
        return $this->recipientCollection;
    }
}
