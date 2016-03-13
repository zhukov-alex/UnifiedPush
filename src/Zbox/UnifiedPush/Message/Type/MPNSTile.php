<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message\Type;

/**
 * Class MPNSTile
 * @package Zbox\UnifiedPush\Message\Type
 */
class MPNSTile extends MPNSBase
{
    const MESSAGE_TYPE = 'tile';

    const DELAY_INTERVAL_IMMEDIATE  = 1;
    const DELAY_INTERVAL_450        = 11;
    const DELAY_INTERVAL_900        = 21;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $backgroundImage;

    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $backTitle;

    /**
     * @var string
     */
    private $backBackgroundImage;

    /**
     * @var string
     */
    private $backContent;

    /**
     * @return string
     */
    public function getBackBackgroundImage()
    {
        return $this->backBackgroundImage;
    }

    /**
     * @param string $backBackgroundImage
     * @return $this
     */
    public function setBackBackgroundImage($backBackgroundImage)
    {
        $this->backBackgroundImage = $backBackgroundImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackContent()
    {
        return $this->backContent;
    }

    /**
     * @param string $backContent
     * @return $this
     */
    public function setBackContent($backContent)
    {
        $this->backContent = $backContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackTitle()
    {
        return $this->backTitle;
    }

    /**
     * @param string $backTitle
     * @return $this
     */
    public function setBackTitle($backTitle)
    {
        $this->backTitle = $backTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundImage()
    {
        return $this->backgroundImage;
    }

    /**
     * @param string $backgroundImage
     * @return $this
     */
    public function setBackgroundImage($backgroundImage)
    {
        $this->backgroundImage = $backgroundImage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
