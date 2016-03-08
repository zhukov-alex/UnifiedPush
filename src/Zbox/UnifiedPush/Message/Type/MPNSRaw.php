<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message\Type;

use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class MPNSRaw
 * @package Zbox\UnifiedPush\Message\Type
 */
class MPNSRaw extends MPNSBase
{
    const MESSAGE_TYPE = 'raw';

    const DELAY_INTERVAL_IMMEDIATE  = 3;
    const DELAY_INTERVAL_450        = 13;
    const DELAY_INTERVAL_900        = 23;

    /**
     * Custom payload parameters
     *
     * @var array
     */
    private $_payload;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->setMessageIdentifier(uniqid());
        $this->recipientCollection = new \ArrayIterator();

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createPayload()
    {
        $message      = new \DOMDocument("1.0", "utf-8");
        $baseElement  = $message->createElement("wp:Notification");
        $baseElement->setAttribute("xmlns:wp", "WPNotification");
        $message->appendChild($baseElement);

        $rootElement = $message->createElement("root");
        $baseElement->appendChild($rootElement);

        foreach ($this->_payload as $key => $value) {
            $element = $message->createElement($key, $value);
            $rootElement->appendChild($element);
        }

        return $message;
    }

    /**
     * @param string $name
     */
    public function __get($name)
    {
        if (empty($this->_payload[$name])) {
            throw new InvalidArgumentException(sprintf("Payload parameter '%s' is not defined", $name));
        }

        return $this->_payload[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_payload[$name] = htmlspecialchars($value);
    }
}
