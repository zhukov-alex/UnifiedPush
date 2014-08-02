<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Exception\DomainException;

/**
 * Class NotificationServices
 * @package Zbox\UnifiedPush\NotificationService
 */
class NotificationServices
{
    const APPLE_PUSH_NOTIFICATIONS_SERVICE     = 'APNS';
    const GOOGLE_CLOUD_MESSAGING               = 'GCM';

    /**
     * Checks if notification service is supported
     *
     * @param string $serviceName
     * @return string
     * @throws DomainException
     */
    public static function validateServiceName($serviceName)
    {
        if (!in_array($serviceName, array(
            self::APPLE_PUSH_NOTIFICATIONS_SERVICE,
            self::GOOGLE_CLOUD_MESSAGING
        ))) {
            throw new DomainException(sprintf("Notification service '%s' is not supported.", $serviceName));
        }
        return $serviceName;
    }
}
