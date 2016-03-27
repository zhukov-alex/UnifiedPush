<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService;

use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsInterface;
use Zbox\UnifiedPush\Notification\Notification;

interface ServiceClientInterface
{
    public function getClientConnection();

    public function setServiceURL($serviceURL);
    public function getServiceURL();

    public function setCredentials(CredentialsInterface $credentials);
    public function getCredentials();

    public function setNotification(Notification $notification);
    public function getNotificationOrThrowException();

    /**
     * @return ResponseInterface
     */
    public function sendRequest();
}
