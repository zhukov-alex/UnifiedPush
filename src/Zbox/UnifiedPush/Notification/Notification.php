<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

use Zbox\UnifiedPush\NotificationService\NotificationServices;

/**
 * Class Notification
 * @package Zbox\UnifiedPush\Notification
 */
class Notification
{
    /**
     * @var string
     */
    protected $type;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = NotificationServices::validateServiceName($type);
        return $this;
    }

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
