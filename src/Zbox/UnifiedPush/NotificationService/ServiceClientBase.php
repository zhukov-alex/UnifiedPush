<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Message\Notification;
use Zbox\UnifiedPush\Exception\InvalidArgumentException;

/**
 * Class ServiceClientBase
 * @package Zbox\UnifiedPush\NotificationService
 */
abstract class ServiceClientBase implements ServiceClientInterface
{
    /**
     * @var array
     */
    protected $serviceURL;

    /**
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * @var Notification
     */
    protected $notification;

    /**
     * @var mixed
     */
    protected $serviceClient;

    /**
     * @param array $serviceUrl
     * @param CredentialsInterface $credentials
     */
    public function __construct($serviceUrl, CredentialsInterface $credentials)
    {
        $this->setServiceURL($serviceUrl);
        $this->setCredentials($credentials);

        $this->createClient();

        return $this;
    }

    abstract protected function createClient();

    /**
     * @param array $serviceURL
     * @return $this
     */
    public function setServiceURL($serviceURL)
    {
        $this->serviceURL = $serviceURL;
        return $this;
    }

    /**
     * @return array
     */
    public function getServiceURL()
    {
        return $this->serviceURL;
    }

    /**
     * @param CredentialsInterface $credentials
     * @return $this
     */
    public function setCredentials(CredentialsInterface $credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * @return CredentialsInterface
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param Notification $notification
     * @return $this
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotificationOrThrowException()
    {
        if (empty($this->notification)) {
            throw new InvalidArgumentException('Missing notification');
        }

        return $this->notification;
    }

    /**
     * @return mixed
     */
    public function getClientConnection()
    {
        return $this->serviceClient;
    }
}
