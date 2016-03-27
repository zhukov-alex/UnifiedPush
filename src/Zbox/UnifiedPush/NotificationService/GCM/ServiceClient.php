<?php

/*
 * (c) Alexander Zhukov <zbox82@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zbox\UnifiedPush\NotificationService\GCM;

use Zbox\UnifiedPush\NotificationService\ResponseInterface;
use Zbox\UnifiedPush\NotificationService\ServiceClientBase;
use Zbox\UnifiedPush\Utils\ClientCredentials\DTO\AuthToken;
use Zbox\UnifiedPush\Exception\ClientException;
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
     * @throws ClientException
     * @return ResponseInterface
     */
    public function sendRequest()
    {
        $notification = $this->getNotificationOrThrowException();

        try {
            $connection  = $this->getClientConnection();
            $serviceURL  = $this->getServiceURL();

            $response =
                $connection->post(
                    $serviceURL['url'],
                    $this->getHeaders(),
                    $notification->getPayload()
                );

            $connection->getClient()->flush();

        } catch (\Exception $e) {
            throw new ClientException($e->getMessage());
        }

        return new Response($response, $notification->getRecipients());
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        /** @var AuthToken $credentials */
        $credentials = $this->getCredentials();

        return
            array(
                sprintf('Authorization: key=%s', $credentials->getAuthToken()),
                'Content-Type: application/json'
            );
    }
}
