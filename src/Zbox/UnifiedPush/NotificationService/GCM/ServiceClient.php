<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Exception\ClientException;
use Zbox\UnifiedPush\Exception\BadMethodCallException;
use Buzz\Browser;
use Buzz\Client\MultiCurl;

/**
 * Class ServiceClient
 * @package Zbox\UnifiedPush\NotificationService\GCM
 */
class ServiceClient extends ServiceClientBase
{
    /**
     * Initializing HTTP client
     *
     * @return $this
     */
    protected function createClient()
    {
        $client = new MultiCurl();
        $client->setVerifyPeer(false);

        $this->serviceClient = new Browser($client);

        return $this;
    }

    /**
     * When the message is processed successfully, the HTTP response has a 200 status.
     * Body contains more information about the status of the message. When the request is rejected,
     * the HTTP response contains a non-200 status code.
     *
     * @param array $notification
     * @throws ClientException
     * @return bool
     */
    public function sendNotification($notification)
    {
        try {
            $connection  = $this->getClientConnection();
            $serviceURL  = $this->getServiceURL();
            $credentials = $this->getCredentials();

            $headers[] = 'Authorization: key='.$credentials->getAuthToken();
            $headers[] = 'Content-Type: application/json';

            $response = $connection->post($serviceURL['url'], $headers, $notification['body']);
            $connection->getClient()->flush();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        new Response($response, $notification['recipients']);

        return true;
    }

    /**
     * No feedback service available in GCM
     *
     * @throws BadMethodCallException
     */
    public function readFeedback()
    {
        throw new BadMethodCallException("No feedback service available in GCM");
    }
}
