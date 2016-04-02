<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Utils;

/**
 * Class JsonEncoder
 * @package Zbox\UnifiedPush\Utils
 */
class JsonEncoder
{
    /**
     * @param array $data
     * @return string
     */
    public static function jsonEncode($data)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=') && defined('JSON_UNESCAPED_UNICODE')) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($data);
        }
    }
}
