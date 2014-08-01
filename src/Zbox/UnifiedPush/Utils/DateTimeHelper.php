<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils;

/**
 * Class DateTimeHelper
 * @package Zbox\UnifiedPush\Utils
 */
class DateTimeHelper
{
    const TIMEZONE_UTC = 'UTC';

    public static function updateTimezoneToUniversal(\DateTime $dateTime)
    {
        if ($dateTime->getTimezone()->getName() != self::TIMEZONE_UTC) {
            $dateTime->setTimezone(new \DateTimeZone(self::TIMEZONE_UTC));
        }
        return $dateTime;
    }
}