<?php

namespace Zbox\UnifiedPush\Message;

/**
 * Class MessageCollection
 * @package Zbox\UnifiedPush\Message
 */
class MessageCollection
{
    /**
     * @var \ArrayIterator
     */
    protected $messageCollection;

    /**
     * @param array $messages
     */
    public function __construct(array $messages = array())
    {
        $this->messageCollection = new \ArrayIterator();

        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function addMessage(MessageInterface $message)
    {
        $this->messageCollection->append($message);
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getMessageCollection()
    {
        $this->messageCollection->rewind();

        return $this->messageCollection;
    }
}
