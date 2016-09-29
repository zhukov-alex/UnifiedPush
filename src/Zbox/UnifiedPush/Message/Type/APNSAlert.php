<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\Message\Type;

/**
 * Class APNSAlert
 * @package Zbox\UnifiedPush\Message\Type
 */
class APNSAlert
{
    /**
     * @var string|null
     */
    private $body;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $titleLocKey;

    /**
     * @var array|null
     */
    private $titleLocArgs;

    /**
     * @var string|null
     */
    private $actionLocKey;

    /**
     * @var string|null
     */
    private $locKey;

    /**
     * @var array|null
     */
    private $locArgs;

    /**
     * @var string|null
     */
    private $launchImage;

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null|string $body
     * @return APNSAlert
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     * @return APNSAlert
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitleLocKey()
    {
        return $this->titleLocKey;
    }

    /**
     * @param null|string $titleLocKey
     * @return APNSAlert
     */
    public function setTitleLocKey($titleLocKey)
    {
        $this->titleLocKey = $titleLocKey;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTitleLocArgs()
    {
        return $this->titleLocArgs;
    }

    /**
     * @param array|null $titleLocArgs
     * @return APNSAlert
     */
    public function setTitleLocArgs(array $titleLocArgs)
    {
        $this->titleLocArgs = $titleLocArgs;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getActionLocKey()
    {
        return $this->actionLocKey;
    }

    /**
     * @param null|string $actionLocKey
     * @return APNSAlert
     */
    public function setActionLocKey($actionLocKey)
    {
        $this->actionLocKey = $actionLocKey;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLocKey()
    {
        return $this->locKey;
    }

    /**
     * @param null|string $locKey
     * @return APNSAlert
     */
    public function setLocKey($locKey)
    {
        $this->locKey = $locKey;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getLocArgs()
    {
        return $this->locArgs;
    }

    /**
     * @param array|null $locArgs
     * @return APNSAlert
     */
    public function setLocArgs(array $locArgs)
    {
        $this->locArgs = $locArgs;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLaunchImage()
    {
        return $this->launchImage;
    }

    /**
     * @param null|string $launchImage
     * @return APNSAlert
     */
    public function setLaunchImage($launchImage)
    {
        $this->launchImage = $launchImage;
        return $this;
    }
}
