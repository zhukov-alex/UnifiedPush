<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message\Type;

use Zbox\UnifiedPush\Exception\InvalidArgumentException;
use Zbox\UnifiedPush\NotificationService\NotificationServices;

/**
 * Class MPNSToast
 * @package Zbox\UnifiedPush\Message\Type
 */
class MPNSToast extends MPNSBase
{
    const MESSAGE_TYPE = 'toast';

    const DELAY_INTERVAL_IMMEDIATE  = 2;
    const DELAY_INTERVAL_450        = 12;
    const DELAY_INTERVAL_900        = 22;

    /**
     * @var string
     */
    private $text1;

    /**
     * @var string
     */
    private $text2;

    /**
     * @var string
     */
    private $param;

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setParam($param)
    {
        $this->param = $param;
        return $this;
    }

    /**
     * @return string
     */
    public function getText1()
    {
        return $this->text1;
    }

    /**
     * @param string $text1
     * @return $this
     */
    public function setText1($text1)
    {
        $this->text1 = $text1;
        return $this;
    }

    /**
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * @param string $text2
     * @return $this
     */
    public function setText2($text2)
    {
        $this->text2 = $text2;
        return $this;
    }
}
