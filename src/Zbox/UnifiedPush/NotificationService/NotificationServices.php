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
    const MICROSOFT_PUSH_NOTIFICATIONS_SERVICE = 'MPNS';

    const CREDENTIALS_NULL          = 1;
    const CREDENTIALS_CERTIFICATE   = 2;
    const CREDENTIALS_AUTH_TOKEN    = 3;

    /**
     * List of supported notification services
     *
     * @return array
     */
    public static function getAvailableServices()
    {
        return
            array(
                self::APPLE_PUSH_NOTIFICATIONS_SERVICE,
                self::GOOGLE_CLOUD_MESSAGING,
                self::MICROSOFT_PUSH_NOTIFICATIONS_SERVICE
            );
    }

    /**
     * Checks if notification service is supported
     *
     * @param string $serviceName
     * @return string
     * @throws DomainException
     */
    public static function validateServiceName($serviceName)
    {
        if (!in_array($serviceName, self::getAvailableServices())) {
            throw new DomainException(sprintf("Notification service '%s' is not supported.", $serviceName));
        }
        return $serviceName;
    }

    /**
     * @param string $serviceName
     * @return int
     */
    public static function getCredentialsTypeByService($serviceName)
    {
        self::validateServiceName($serviceName);

        $credentials = array(
            self::APPLE_PUSH_NOTIFICATIONS_SERVICE      => self::CREDENTIALS_CERTIFICATE,
            self::GOOGLE_CLOUD_MESSAGING                => self::CREDENTIALS_AUTH_TOKEN,
            self::MICROSOFT_PUSH_NOTIFICATIONS_SERVICE  => self::CREDENTIALS_CERTIFICATE
        );

        return $credentials[$serviceName];
    }
}
