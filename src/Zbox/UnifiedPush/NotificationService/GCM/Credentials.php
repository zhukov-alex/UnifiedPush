<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\NotificationService\CredentialsInterface;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class Credentials
 * @package PN\NotificationService\GCM
 */
class Credentials implements CredentialsInterface
{
    /**
     * An API key that gives the application server authorized access to GCM
     *
     * @var string
     */
    private $authToken;

    /**
     * @param array $credentials
     */
    public function __construct($credentials)
    {
        $object = (object) $credentials;
        $this->setAuthToken($object);
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param \stdClass $credentials
     * @return $this
     */
    public function setAuthToken($credentials)
    {
        if (empty($credentials->authToken)) {
            throw new InvalidArgumentException("An API key is required");
        }

        $this->authToken = $credentials->authToken;

        return $this;
    }
}
