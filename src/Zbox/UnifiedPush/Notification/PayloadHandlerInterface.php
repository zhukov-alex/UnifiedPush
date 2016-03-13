<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Notification;

use Zbox\UnifiedPush\Message\MessageInterface;

/**
 * Interface PayloadHandlerInterface
 * @package Zbox\UnifiedPush\Notification
 */
interface PayloadHandlerInterface
{
    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function isSupported(MessageInterface $message);

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function setMessage(MessageInterface $message);

    /**
     * @return int
     */
    public function getPayloadMaxLength();

    /**
     * @return array
     */
    public function getCustomNotificationData();

    /**
     * @return mixed
     */
    public function createPayload();

    /**
     * @param mixed $payload
     * @return string
     */
    public function packPayload($payload);
}
