<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

/**
 * Class Notification
 * @package Zbox\UnifiedPush\Notification
 */
class Notification
{
    /**
     * @var string
     */
    protected $payload;

    /**
     * @var \ArrayIterator
     */
    protected $recipients;

    /**
     * Custom properties
     *
     * @var array
     */
    private $customNotificationData = array();

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param \ArrayIterator $recipients
     * @return $this
     */
    public function setRecipients(\ArrayIterator $recipients)
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomNotificationData()
    {
        return $this->customNotificationData;
    }

    /**
     * @param array $customNotificationData
     * @return $this
     */
    public function setCustomNotificationData($customNotificationData)
    {
        $this->customNotificationData = $customNotificationData;
        return $this;
    }
}
